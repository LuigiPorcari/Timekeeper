<?php

namespace App\Services;

use App\Models\Race;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportPrimaNotaExcelExporter
{
    public function buildFromTemplate(string $templatePath, Race $race, array $data): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $spreadsheet = IOFactory::load($templatePath);

        // Nel tuo file i dati sono su "Foglio1"
        $sheet = $spreadsheet->getSheetByName('Foglio1') ?? $spreadsheet->getActiveSheet();

        $originalWidths = [];
        foreach (range('A', 'P') as $col) {
            $dim = $sheet->getColumnDimension($col);

            $originalWidths[$col] = [
                'auto' => $dim->getAutoSize(),
                'width' => $dim->getWidth(),
            ];
        }

        // 1) Trovo la riga dove c'è "Nominativo Crono" in colonna B
        $headerRow = $this->findRowByValue($sheet, 'B', 'Nominativo Crono');
        if (!$headerRow) {
            throw new \RuntimeException('Impossibile trovare "Nominativo Crono" nel template.');
        }

        // struttura: headerRow=127, subHeaderRow=128, firstDataRow=129
        $firstDataRow = $headerRow + 2;

        // 2) Trovo la riga "TOTALE" (colonna P)
        $totalRow = $this->findRowByValue($sheet, 'P', 'TOTALE', $firstDataRow, $firstDataRow + 80);
        if (!$totalRow) {
            throw new \RuntimeException('Impossibile trovare la riga "TOTALE" nel template (colonna P).');
        }

        // 3) Preparo le righe da esportare: una riga per crono
        $exportRows = $this->buildExportRows($data); // array di righe normalizzate
        $needed = count($exportRows);

        // 4) Adeguo numero righe tra firstDataRow e totalRow-1
        $existing = max(0, $totalRow - $firstDataRow);

        if ($needed > $existing) {
            $toInsert = $needed - $existing;
            // inserisco prima della riga totale (sposta in basso la riga totale e tutto ciò che segue)
            $sheet->insertNewRowBefore($totalRow, $toInsert);
            $totalRow += $toInsert;
        } elseif ($needed < $existing) {
            $toRemove = $existing - $needed;
            // rimuovo righe in eccesso prima della riga totale
            $sheet->removeRow($firstDataRow + $needed, $toRemove);
            $totalRow -= $toRemove;
        }

        // 5) Copio stile dalla "riga modello" (firstDataRow) a tutte le righe dati
        // (così mantiene formattazioni bordi ecc.)
        $templateRowIdx = $firstDataRow;
        for ($r = $firstDataRow; $r < $firstDataRow + $needed; $r++) {
            if ($r === $templateRowIdx)
                continue;
            $this->copyRowStyle($sheet, $templateRowIdx, $r, 'A', 'P');
            $this->copyRowMerges($sheet, $templateRowIdx, $r);
        }

        // 6) Scrivo i dati riga per riga + formule
        $coeffCell = 'G' . $headerRow; // es: G127
        $diariaGCell = 'C' . $headerRow; // es: C127
        $diariaSCell = 'D' . $headerRow; // es: D127

        $start = $race->date_of_race ? Carbon::parse($race->date_of_race)->startOfDay() : null;
        $end = $race->date_end ? Carbon::parse($race->date_end)->startOfDay() : null;

        $daysCount = 1; // default
        if ($start) {
            if ($end) {
                if ($end->lt($start)) {
                    [$start, $end] = [$end, $start];
                }
                $daysCount = $start->diffInDays($end) + 1; // inclusivo
            } else {
                $daysCount = 1;
            }
        }

        $sheet->setCellValue('Q2', (int) (1)); //da capire
        $sheet->setCellValue('G3', (string) ($race->ente_fatturazione ?? ''));
        $sheet->setCellValue('G5', (string) ($race->note ?? ''));
        $sheet->setCellValue('G7', (int) (1)); //da capire
        $sheet->setCellValue('M7', (string) ($race->place ?? ''));
        $sheet->setCellValue('B9', (string) ($race->type ?? ''));
        $sheet->setCellValue('G9', Carbon::parse($race->date_of_race ?? '-')->format('d/m/Y'));
        $sheet->setCellValue('M9', Carbon::parse($race->date_end ?? '-')->format('d/m/Y'));
        $sheet->setCellValue('P9', (int) $daysCount);

        for ($i = 0; $i < $needed; $i++) {
            $r = $firstDataRow + $i;
            $row = $exportRows[$i];

            // Colonne (da template):
            // A = progressivo
            // B = Nominativo
            // C = serv. Gior (ore ord)
            // D = serv. Spec (ore spec)
            // E = Tariffa (codice - se non ce l’hai puoi lasciare vuoto)
            // F = km
            // G = importo km (formula)
            // H = Biglietti Viaggio
            // I = Vitto
            // J = Alloggio
            // K = Varie
            // L = Vitto (non doc) -> se non lo gestisci, 0
            // M = diarie GIORN (formula)
            // N = diarie SPEC (formula)
            // O = totale (formula)
            // P = note

            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $row['name']);

            $sheet->setCellValue("C{$r}", $row['ordHours']);
            $sheet->setCellValue("D{$r}", $row['specHours']);
            $sheet->setCellValue("E{$r}", $row['tariffa'] ?? '');

            $sheet->setCellValue("F{$r}", $row['km']);

            $sheet->setCellValue("H{$r}", $row['pedaggi']);
            $sheet->setCellValue("I{$r}", $row['vitto']);
            $sheet->setCellValue("J{$r}", $row['alloggio']);
            $sheet->setCellValue("K{$r}", $row['spese_varie']);
            $sheet->setCellValue("L{$r}", 0);

            $sheet->setCellValue("P{$r}", $row['note'] ?? '');

            // Formule come da template:
            $sheet->setCellValue("G{$r}", "=F{$r}*{$coeffCell}");
            $sheet->setCellValue("M{$r}", "=C{$r}*{$diariaGCell}");
            $sheet->setCellValue("N{$r}", "=D{$r}*{$diariaSCell}");
            $sheet->setCellValue("O{$r}", "=G{$r}+H{$r}+I{$r}+J{$r}+K{$r}+L{$r}+M{$r}+N{$r}");

            $this->normalizeDataRowStyle($sheet, $r, 'A', 'P');
        }

        // 7) Aggiorno formula del totale (colonna O nella riga TOTALE)
        if ($needed > 0) {
            $start = $firstDataRow;
            $end = $firstDataRow + $needed - 1;
            $sheet->setCellValue("O{$totalRow}", "=SUM(O{$start}:O{$end})");
        } else {
            $sheet->setCellValue("O{$totalRow}", "=0");
        }

        foreach ($originalWidths as $col => $info) {
            $dim = $sheet->getColumnDimension($col);

            $dim->setAutoSize(false);                // IMPORTANT: spegni autosize
            $dim->setWidth($info['width']);          // rimette la larghezza del template
        }

        $sheet->getColumnDimension('I')->setAutoSize(true);


        // ...dopo autosize, impone un massimo
        $maxWidth = 25; // prova 20/25/30

        $dim = $sheet->getColumnDimension('I');
        $w = $dim->getWidth();
        if ($w > $maxWidth) {
            $dim->setAutoSize(false);
            $dim->setWidth($maxWidth);
        }


        return $spreadsheet;
    }

    /**
     * Converte la struttura del builder in righe "per crono".
     * ordHours/specHours: somma su tutti i giorni delle ore ord/spec (da perDay->service).
     */
    private function buildExportRows(array $data): array
    {
        $rows = $data['rows'] ?? [];
        $days = $data['days'] ?? [];

        $out = [];

        foreach ($rows as $r) {
            $u = $r['user'];
            $entry = $r['entry'];

            $ordSum = 0.0;
            $specSum = 0.0;

            foreach ($days as $day) {
                $drow = $r['perDay'][$day] ?? null;
                $service = $drow['service'] ?? [];
                $ordSum += (float) ($service['ordHours'] ?? 0);
                $specSum += (float) ($service['specHours'] ?? 0);
            }

            $out[] = [
                'name' => trim(($u->surname ?? '') . ' ' . ($u->name ?? '')),
                'ordHours' => round($ordSum, 2),
                'specHours' => round($specSum, 2),

                // se non hai un valore "tariffa" nel DB, lascia vuoto
                'tariffa' => '',

                'km' => (float) ($entry->km ?? 0),

                // mapping spese (adatta se vuoi distinguere biglietti/trasporto)
                'pedaggi' => (float) ($entry->pedaggi ?? 0),
                'vitto' => (float) ($entry->vitto ?? 0),
                'alloggio' => (float) ($entry->alloggio ?? 0),
                'spese_varie' => (float) ($entry->spese_varie ?? 0),

                'note' => (string) ($entry->note ?? ''),
            ];
        }

        return $out;
    }

    private function findRowByValue(Worksheet $sheet, string $colLetter, string $needle, int $fromRow = 1, ?int $toRow = null): ?int
    {
        $toRow = $toRow ?? $sheet->getHighestRow();
        $needleNorm = trim(mb_strtolower($needle));

        for ($r = $fromRow; $r <= $toRow; $r++) {
            $val = $sheet->getCell($colLetter . $r)->getValue();
            $valNorm = trim(mb_strtolower((string) $val));
            if ($valNorm === $needleNorm) {
                return $r;
            }
        }
        return null;
    }

    private function copyRowStyle(Worksheet $sheet, int $fromRow, int $toRow, string $fromCol, string $toCol): void
    {
        $startColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($fromCol);
        $endColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($toCol);

        for ($col = $startColIndex; $col <= $endColIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);

            $fromCell = $colLetter . $fromRow;
            $toCell = $colLetter . $toRow;

            // copia stile cella per cella
            $sheet->duplicateStyle($sheet->getStyle($fromCell), $toCell);

            // copia anche il valore "vuoto" della cella modello se vuoi (di solito NO)
            // $sheet->setCellValue($toCell, $sheet->getCell($fromCell)->getValue());
        }

        // copia altezza riga
        $sheet->getRowDimension($toRow)->setRowHeight(
            $sheet->getRowDimension($fromRow)->getRowHeight()
        );
    }


    private function copyRowMerges(Worksheet $sheet, int $fromRow, int $toRow): void
    {
        foreach ($sheet->getMergeCells() as $mergedRange) {
            // es: "B129:F129"
            [$start, $end] = explode(':', $mergedRange);

            [$startCol, $startRow] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($start);
            [$endCol, $endRow] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($end);

            // se il merge è ESATTAMENTE sulla riga modello, lo replico sulla riga target
            if ((int) $startRow === $fromRow && (int) $endRow === $fromRow) {
                $newRange = $startCol . $toRow . ':' . $endCol . $toRow;
                $sheet->mergeCells($newRange);
            }
        }
    }

    private function normalizeDataRowStyle(Worksheet $sheet, int $row, string $fromCol = 'A', string $toCol = 'P'): void
    {
        $range = "{$fromCol}{$row}:{$toCol}{$row}";

        $style = $sheet->getStyle($range);

        // niente grassetto
        $style->getFont()->setBold(false);

        // niente indentazioni strane
        $style->getAlignment()->setIndent(0);

        // allineamento standard (come riga sopra)
        $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_GENERAL);
        $style->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    }


}
