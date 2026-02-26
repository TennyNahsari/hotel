<?php

namespace App\Exports;

use App\Models\LaundryOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class LaundryOrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = LaundryOrder::with(['booking.guest', 'createdBy']);

        // Filter by date range
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Booking Number',
            'Guest Name',
            'Weight (kg)',
            'Price per kg',
            'Total Amount',
            'Order Date',
            'Created By',
            'Notes',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->booking?->booking_number ?? '-',
            $order->booking?->guest?->name ?? '-',
            number_format($order->weight_kg, 2, '.', ''),
            number_format($order->price_per_kg, 2, '.', ''),
            number_format($order->total_amount, 2, '.', ''),
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
                    'startColor' => ['rgb' => '6366F1'],
                ],
            ],
        ];
    }
}
