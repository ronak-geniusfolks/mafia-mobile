<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttachmentsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected Collection $records,
        protected string $tab = 'invoice'  // 'invoice' | 'purchase'
    ) {}

    // ── Data rows ─────────────────────────────────────────────────────────────

    public function collection(): Collection
    {
        return $this->records->map(function ($att) {
            $uploadedBy = $att->uploaded_by
                ? ($att->uploader?->name ?? 'User #' . $att->uploaded_by)
                : 'Mobile Upload';

            $uploadedOn = $att->created_at
                ? Carbon::parse($att->created_at)->format('d-m-Y H:i')
                : '—';

            if ($this->tab === 'invoice') {
                $parent = $att->attachable;
                return [
                    $parent?->invoice_no ?? '—',
                    $parent?->invoice_date
                        ? Carbon::parse($parent->invoice_date)->format('d-m-Y')
                        : '—',
                    $parent?->customer_name  ?? '—',
                    $parent?->customer_no    ?? '—',
                    $att->label              ?? '—',
                    $att->file_name,
                    $att->isImage() ? 'Image' : ($att->isPdf() ? 'PDF' : ($att->file_type ?? '—')),
                    $att->formatted_size,
                    $uploadedBy,
                    $uploadedOn,
                    $att->url,
                ];
            }

            // purchase tab
            $parent = $att->attachable;
            return [
                $att->attachable_id,
                $parent?->model           ?? '—',
                $parent?->imei            ?? '—',
                $parent?->purchase_date
                    ? Carbon::parse($parent->purchase_date)->format('d-m-Y')
                    : '—',
                $parent?->purchase_from   ?? '—',
                $att->label               ?? '—',
                $att->file_name,
                $att->isImage() ? 'Image' : ($att->isPdf() ? 'PDF' : ($att->file_type ?? '—')),
                $att->formatted_size,
                $uploadedBy,
                $uploadedOn,
                $att->url,
            ];
        });
    }

    // ── Column headings ───────────────────────────────────────────────────────

    public function headings(): array
    {
        if ($this->tab === 'invoice') {
            return [
                'Invoice No.',
                'Invoice Date',
                'Customer Name',
                'Customer No.',
                'Document Label',
                'File Name',
                'File Type',
                'File Size',
                'Uploaded By',
                'Uploaded On',
                'File URL',
            ];
        }

        return [
            'Stock ID',
            'Model',
            'IMEI',
            'Purchase Date',
            'Purchase From',
            'Document Label',
            'File Name',
            'File Type',
            'File Size',
            'Uploaded By',
            'Uploaded On',
            'File URL',
        ];
    }

    // ── Styles ────────────────────────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        // Bold blue header row
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 10,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1a56db'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    // ── Sheet title ───────────────────────────────────────────────────────────

    public function title(): string
    {
        return $this->tab === 'invoice' ? 'Invoice Documents' : 'Stock Documents';
    }
}
