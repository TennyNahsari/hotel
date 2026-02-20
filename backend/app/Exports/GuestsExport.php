<?php

namespace App\Exports;

use App\Models\Guest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class GuestsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $nationality;

    public function __construct($search = null, $nationality = null)
    {
        $this->search = $search;
        $this->nationality = $nationality;
    }

    public function collection()
    {
        $query = Guest::withCount('bookings');

        // Filter by search
        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id_card_number', 'like', "%{$search}%");
            });
        }

        // Filter by nationality
        if ($this->nationality) {
            $query->where('nationality', $this->nationality);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Guest ID',
            'Name',
            'Email',
            'Phone',
            'ID Card Type',
            'ID Card Number',
            'Nationality',
            'Birth Date',
            'Address',
            'Total Bookings',
            'Is Repeat Guest',
            'Total Stays',
            'Registered Date',
        ];
    }

    public function map($guest): array
    {
        // Format ID card type
        $idCardTypeLabels = [
            'ktp' => 'KTP',
            'passport' => 'Passport',
            'sim' => 'SIM',
            'other' => 'Other',
        ];
        $idCardType = isset($guest->id_card_type) ? ($idCardTypeLabels[$guest->id_card_type] ?? $guest->id_card_type) : '-';

        return [
            $guest->id,
            $guest->name,
            $guest->email ?: '-',
            $guest->phone,
            $idCardType,
            $guest->id_card_number ?: '-',
            $guest->nationality ?: '-',
            $guest->birth_date ? $guest->birth_date->format('Y-m-d') : '-',
            $guest->address ?: '-',
            $guest->bookings_count ?? 0,
            $guest->is_repeat_guest ? 'Yes' : 'No',
            $guest->total_stays ?? 0,
            $guest->created_at ? $guest->created_at->format('Y-m-d H:i:s') : '-',
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
