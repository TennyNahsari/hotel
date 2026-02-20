<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RoomsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = Room::with('roomType');

        // Apply filters if provided
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['room_type_id'])) {
            $query->where('room_type_id', $this->filters['room_type_id']);
        }

        if (!empty($this->filters['floor'])) {
            $query->where('floor', $this->filters['floor']);
        }

        // Only active rooms by default
        if (!isset($this->filters['include_inactive'])) {
            $query->where('is_active', true);
        }

        return $query->orderBy('room_number')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Room Number',
            'Room Type',
            'Floor',
            'Status',
            'Notes',
            'Active',
            'Created At',
        ];
    }

    /**
     * @var Room $room
     */
    public function map($room): array
    {
        return [
            $room->room_number,
            $room->roomType->name ?? '-',
            $room->floor ?? '-',
            ucfirst(str_replace('_', ' ', $room->status)),
            $room->notes ?? '-',
            $room->is_active ? 'Yes' : 'No',
            $room->created_at->format('Y-m-d H:i:s'),
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
