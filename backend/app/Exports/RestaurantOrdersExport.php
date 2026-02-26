<?php

namespace App\Exports;

use App\Models\RestaurantOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class RestaurantOrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $status;

    public function __construct($startDate = null, $endDate = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    public function collection()
    {
        $query = RestaurantOrder::with([
            'booking.guest', 
            'booking.rooms', 
            'hallBooking.hall',
            'orderItems.menuItem', 
            'createdBy'
        ]);

        // Filter by date range
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Filter by status
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Booking Number',
            'Customer Name',
            'Customer Type',
            'Room/Hall',
            'Total Items',
            'Total Amount',
            'Status',
            'Order Date',
            'Created By',
            'Notes',
        ];
    }

    public function map($order): array
    {
        // Determine customer type and details
        if ($order->booking_id) {
            $bookingNumber = $order->booking?->booking_number ?? '-';
            $customerName = $order->booking?->guest?->name ?? '-';
            $customerType = 'Room Booking';
            $roomOrHall = $order->booking && $order->booking->rooms 
                ? $order->booking->rooms->pluck('room_number')->join(', ')
                : '-';
        } else {
            $bookingNumber = $order->hallBooking?->booking_number ?? '-';
            $customerName = $order->hallBooking?->customer_name ?? '-';
            $customerType = 'Hall Booking';
            $roomOrHall = $order->hallBooking?->hall?->name ?? '-';
        }

        return [
            $order->order_number,
            $bookingNumber,
            $customerName,
            $customerType,
            $roomOrHall,
            $order->orderItems->count(),
            number_format($order->total_amount, 2, '.', ''),
            ucfirst($order->status),
            $order->created_at->format('Y-m-d H:i:s'),
            $order->createdBy?->name ?? '-',
            $order->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
