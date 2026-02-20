<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $paymentType;
    protected $paymentMethod;

    public function __construct($startDate = null, $endDate = null, $paymentType = null, $paymentMethod = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentType = $paymentType;
        $this->paymentMethod = $paymentMethod;
    }

    public function collection()
    {
        $query = Payment::with(['booking.guest', 'booking.rooms.roomType', 'processedBy']);

        // Filter by date range (payment date)
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Filter by payment type
        if ($this->paymentType) {
            $query->where('payment_type', $this->paymentType);
        }

        // Filter by payment method
        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Payment Number',
            'Booking Number',
            'Guest Name',
            'Guest Email',
            'Guest Phone',
            'Room Numbers',
            'Payment Type',
            'Payment Method',
            'Amount',
            'Reference Number',
            'Payment Date',
            'Processed By',
            'Notes',
        ];
    }

    public function map($payment): array
    {
        // Get room numbers
        $roomNumbers = $payment->booking && $payment->booking->rooms 
            ? $payment->booking->rooms->pluck('room_number')->join(', ')
            : '-';

        // Format payment type
        $paymentTypeLabels = [
            'deposit' => 'Deposit',
            'partial' => 'Partial Payment',
            'full' => 'Full Payment',
            'refund' => 'Refund',
            'extra_charge' => 'Extra Charge',
        ];
        $paymentType = $paymentTypeLabels[$payment->payment_type] ?? $payment->payment_type;

        // Format payment method
        $paymentMethodLabels = [
            'cash' => 'Cash',
            'transfer' => 'Bank Transfer',
            'qris' => 'QRIS',
            'card' => 'Card',
            'other' => 'Other',
        ];
        $paymentMethod = $paymentMethodLabels[$payment->payment_method] ?? $payment->payment_method;

        return [
            $payment->payment_number,
            $payment->booking ? $payment->booking->booking_number : '-',
            $payment->booking && $payment->booking->guest ? $payment->booking->guest->name : '-',
            $payment->booking && $payment->booking->guest ? $payment->booking->guest->email : '-',
            $payment->booking && $payment->booking->guest ? $payment->booking->guest->phone : '-',
            $roomNumbers,
            $paymentType,
            $paymentMethod,
            number_format($payment->amount, 2),
            $payment->reference_number ?: '-',
            $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : '-',
            $payment->processedBy ? $payment->processedBy->name : '-',
            $payment->notes ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
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
