<?php

namespace App\Exports;

use App\Models\Outlet;
use App\Models\Transaction;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionReportExcel
{
    public function build(Outlet $outlet): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Where Coffee POS')
            ->setTitle("Laporan transaksi {$outlet->name}")
            ->setSubject('Laporan transaksi dan detail penjualan')
            ->setDescription('Dibuat otomatis oleh Where Coffee POS.');

        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Transaksi');
        $details = $spreadsheet->createSheet();
        $details->setTitle('Detail Item');

        $this->writeSummarySheet($summary, $outlet);
        $this->writeDetailSheet($details, $outlet);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function writeSummarySheet(Worksheet $sheet, Outlet $outlet): void
    {
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI WHERE COFFEE');
        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', "Outlet: {$outlet->name} • Diekspor: ".now($outlet->timezone)->format('d/m/Y H:i'));

        $headers = [
            'No.', 'Invoice', 'Waktu', 'Kasir', 'Pelanggan', 'Metode',
            'Subtotal', 'Diskon', 'Service', 'Pajak', 'Total Akhir', 'Laba Kotor',
        ];
        $sheet->fromArray($headers, null, 'A4');

        $row = 5;
        $number = 1;
        $totals = array_fill_keys(['subtotal', 'discount', 'service', 'tax', 'total', 'profit'], 0.0);

        Transaction::query()
            ->with(['user:id,name', 'customer:id,name'])
            ->forOutlet($outlet)
            ->orderBy('id')
            ->chunkById(500, function ($transactions) use ($sheet, &$row, &$number, &$totals, $outlet): void {
                foreach ($transactions as $transaction) {
                    $sheet->fromArray([
                        $number++,
                        $transaction->invoice_number,
                        $transaction->transacted_at?->timezone($outlet->timezone)->format('d/m/Y H:i:s'),
                        $transaction->user?->name ?? '-',
                        $transaction->customer?->name ?? 'Non-member',
                        $transaction->payment_method,
                        (float) $transaction->subtotal,
                        (float) $transaction->discount_amount,
                        (float) $transaction->service_charge_amount,
                        (float) $transaction->tax_amount,
                        (float) $transaction->grand_total,
                        (float) $transaction->gross_profit,
                    ], null, "A{$row}");

                    $totals['subtotal'] += (float) $transaction->subtotal;
                    $totals['discount'] += (float) $transaction->discount_amount;
                    $totals['service'] += (float) $transaction->service_charge_amount;
                    $totals['tax'] += (float) $transaction->tax_amount;
                    $totals['total'] += (float) $transaction->grand_total;
                    $totals['profit'] += (float) $transaction->gross_profit;
                    $row++;
                }
            });

        $totalRow = $row;
        $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->fromArray([
            $totals['subtotal'], $totals['discount'], $totals['service'],
            $totals['tax'], $totals['total'], $totals['profit'],
        ], null, "G{$totalRow}");

        $this->styleSheet($sheet, 'A4:L4', "A{$totalRow}:L{$totalRow}", 'G', 'L', $totalRow);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:L".max(4, $totalRow - 1));
    }

    private function writeDetailSheet(Worksheet $sheet, Outlet $outlet): void
    {
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', 'DETAIL ITEM PENJUALAN');
        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', "Outlet: {$outlet->name}");

        $headers = [
            'No.', 'Invoice', 'Waktu', 'SKU', 'Barcode', 'Produk', 'Kategori',
            'Harga Modal', 'Harga Jual', 'Qty', 'Subtotal', 'Total Modal', 'Laba',
        ];
        $sheet->fromArray($headers, null, 'A4');

        $row = 5;
        $number = 1;
        $totals = ['qty' => 0, 'subtotal' => 0.0, 'cost' => 0.0, 'profit' => 0.0];

        Transaction::query()
            ->with('items')
            ->forOutlet($outlet)
            ->orderBy('id')
            ->chunkById(250, function ($transactions) use ($sheet, &$row, &$number, &$totals, $outlet): void {
                foreach ($transactions as $transaction) {
                    foreach ($transaction->items as $item) {
                        $sheet->fromArray([
                            $number++,
                            $transaction->invoice_number,
                            $transaction->transacted_at?->timezone($outlet->timezone)->format('d/m/Y H:i:s'),
                            $item->sku,
                            $item->barcode,
                            $item->product_name,
                            $item->category_name,
                            (float) $item->unit_cost,
                            (float) $item->unit_price,
                            (int) $item->quantity,
                            (float) $item->line_subtotal,
                            (float) $item->line_cost,
                            (float) $item->line_profit,
                        ], null, "A{$row}");

                        $totals['qty'] += (int) $item->quantity;
                        $totals['subtotal'] += (float) $item->line_subtotal;
                        $totals['cost'] += (float) $item->line_cost;
                        $totals['profit'] += (float) $item->line_profit;
                        $row++;
                    }
                }
            });

        $totalRow = $row;
        $sheet->mergeCells("A{$totalRow}:I{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->fromArray([
            $totals['qty'], $totals['subtotal'], $totals['cost'], $totals['profit'],
        ], null, "J{$totalRow}");

        $this->styleSheet($sheet, 'A4:M4', "A{$totalRow}:M{$totalRow}", 'H', 'M', $totalRow);
        $sheet->getStyle("J5:J{$totalRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:M".max(4, $totalRow - 1));
    }

    private function styleSheet(
        Worksheet $sheet,
        string $headerRange,
        string $totalRange,
        string $currencyStartColumn,
        string $currencyEndColumn,
        int $totalRow,
    ): void {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC00000');
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setARGB('FF64748B');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(24);

        if ($totalRow >= 5) {
            $sheet->getStyle("{$currencyStartColumn}5:{$currencyEndColumn}{$totalRow}")
                ->getNumberFormat()
                ->setFormatCode('[$Rp-421] #,##0');
        }

        $sheet->getStyle($totalRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF7F1D1D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF1F2']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFC00000']]],
        ]);

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }
}
