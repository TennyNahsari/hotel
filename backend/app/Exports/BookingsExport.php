<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Booking::with(['guest', 'rooms.roomType', 'payments']);

        // Apply date filters for check-in
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('check_in_date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('check_in_date', '<=', $this->filters['end_date']);
        }

        // Apply other filters if provided
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['guest_id'])) {
            $query->where('guest_id', $this->filters['guest_id']);
        }

        return $query->orderBy('check_in_date', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Booking ID',
            'Guest Name',
            'Guest Email',
            'Guest Phone',
            'Check-In Date',
            'Check-Out Date',
            'Nights',
            'Rooms',
            'Number of Guests',
            'Status',
            'Total Amount',
            'Payment Status',
            'Special Requests',
            'Created At',
        ];
    }

    /**
     * @var Booking $booking
     */
    public function map($booking): array
    {
        // Calculate nights
        $checkIn = new \DateTime($booking->check_in_date);
        $checkOut = new \DateTime($booking->check_out_date);
        $nights = $checkOut->diff($checkIn)->days;

        // Get room numbers
        $roomNumbers = $booking->rooms->pluck('room_number')->join(', ');

        // Payment status - get latest payment
        $paymentStatus = '-';
        if ($booking->payments && $booking->payments->isNotEmpty()) {
            $latestPayment = $booking->payments->sortByDesc('created_at')->first();
            $paymentStatus = ucfirst($latestPayment->status);
        }

        return [
            $booking->id,
            $booking->guest->name ?? '-',
            $booking->guest->email ?? '-',
            $booking->guest->phone ?? '-',
            $booking->check_in_date,
            $booking->check_out_date,
            $nights,
            $roomNumbers ?: '-',
            $booking->number_of_guests ?? '-',
            ucfirst(str_replace('_', ' ', $booking->status)),
            number_format($booking->total_amount, 2),
            $paymentStatus,
            $booking->special_requests ?? '-',
            $booking->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
