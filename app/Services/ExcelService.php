<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ExcelService
{
    /**
     * Hardcoded prices for terase materials
     */
    private const TERASE_PRICES = [
        ['produs' => 'FOLIE CRISTAL', 'um' => 'mp', 'pret_achizitie' => 24.00, 'pret_vanzare' => 33.60, 'marja' => 40],
        ['produs' => 'POLIPLAN MARO', 'um' => 'mp', 'pret_achizitie' => 9.50, 'pret_vanzare' => 13.30, 'marja' => 40],
        ['produs' => 'FERMOAR', 'um' => 'buc', 'pret_achizitie' => 35.00, 'pret_vanzare' => 49.00, 'marja' => 40],
        ['produs' => 'CARABINE', 'um' => 'buc', 'pret_achizitie' => 1.50, 'pret_vanzare' => 2.10, 'marja' => 40],
        ['produs' => 'CAPSE OVALE', 'um' => 'buc', 'pret_achizitie' => 0.45, 'pret_vanzare' => 0.60, 'marja' => 40],
        ['produs' => 'CAPSE ROTUNDE', 'um' => 'buc', 'pret_achizitie' => 0.25, 'pret_vanzare' => 0.40, 'marja' => 40],
        ['produs' => 'CATARAMA PLASTIC', 'um' => 'buc', 'pret_achizitie' => 4.00, 'pret_vanzare' => 5.60, 'marja' => 40],
        ['produs' => 'SISTEM RULARE 1m', 'um' => 'buc', 'pret_achizitie' => 420.00, 'pret_vanzare' => 588.00, 'marja' => 40],
        ['produs' => 'SISTEM RULARE 2m', 'um' => 'buc', 'pret_achizitie' => 520.00, 'pret_vanzare' => 728.00, 'marja' => 40],
        ['produs' => 'SISTEM RULARE 3m', 'um' => 'buc', 'pret_achizitie' => 590.00, 'pret_vanzare' => 826.00, 'marja' => 40],
        ['produs' => 'SISTEM RULARE 4m', 'um' => 'buc', 'pret_achizitie' => 680.00, 'pret_vanzare' => 952.00, 'marja' => 40],
        ['produs' => 'SISTEM RULARE 5m', 'um' => 'buc', 'pret_achizitie' => 780.00, 'pret_vanzare' => 1014.00, 'marja' => 30],
        ['produs' => 'Sistem Casetat 2m', 'um' => 'buc', 'pret_achizitie' => 3065.00, 'pret_vanzare' => 4213.00, 'marja' => 37],
        ['produs' => 'MATERIALE+REGIE', 'um' => 'buc', 'pret_achizitie' => 0.00, 'pret_vanzare' => 0.00, 'marja' => 0],
        ['produs' => 'MANOPERA', 'um' => 'h', 'pret_achizitie' => 2.00, 'pret_vanzare' => 75.00, 'marja' => 0],
        ['produs' => 'DEPLASARE /TRENSPORT', 'um' => 'KM', 'pret_achizitie' => 2.00, 'pret_vanzare' => 2.00, 'marja' => 0],
    ];

    /**
     * Get hardcoded terase prices
     */
    public function getTerasePrices(): array
    {
        return self::TERASE_PRICES;
    }

    /**
     * Get calculation instructions for terase
     */
    public function getTeraseCalculationInstructions(): string
    {
        return <<<EOT

=== INSTRUCȚIUNI CALCUL TERASE ===

Pentru calcularea unei oferte de terase, urmează EXACT acești pași:

1. CALCULAREA MATERIALELOR:
   - Identifică materialele necesare din lista de prețuri
   - Calculează pentru fiecare material: Cantitate × Preț Vânzare
   - IMPORTANT: Nu include în suma materialelor: MATERIALE+REGIE, MANOPERA, DEPLASARE/TRANSPORT, PROFIT

2. CALCULAREA MATERIALE+REGIE:
   Formula: MATERIALE+REGIE = SUM(doar materiale fizice) × 1.4
   Exemplu: Dacă suma materialelor fizice = 261.10 lei
   MATERIALE+REGIE = 261.10 × 1.4 = 365.54 lei

3. CALCUL PROFIT:
   Formula: PROFIT = (MATERIALE+REGIE + Manoperă + Transport) × 0.30 (marjă de 30%)
   IMPORTANT: Profitul se calculează pe suma: MATERIALE+REGIE + Manoperă + Transport
   Rezultatul reprezintă diferența de 30% care se adaugă la total!

4. CALCULAREA MANOPEREI:
   - Dacă nu este specificat, estimează 0 ore
   - Calculează: Ore × 75 lei/oră

5. CALCULAREA DEPLASARE/TRANSPORT:
   - Dacă nu este specificat, estimează 0 KM
   - Calculează: KM × 2 lei/KM

6. CALCUL TOTAL FĂRĂ TVA:
   Total fără TVA = MATERIALE+REGIE + PROFIT + Manoperă + Transport
   IMPORTANT: NU adăuga suma materialelor separat! Materialele sunt deja incluse în MATERIALE+REGIE!

7. CALCUL TVA (21%):
   TVA = Total fără TVA × 0.21

8. CALCUL TOTAL CU TVA (Total Final):
   Total cu TVA = Total fără TVA + TVA

EXEMPLU COMPLET DE CALCUL:
Materiale fizice:
- FOLIE CRISTAL: 6 mp × 33.60 lei/mp = 201.60 lei
- FERMOAR: 1 buc × 49.00 lei/buc = 49.00 lei
- CARABINE: 5 buc × 2.10 lei/buc = 10.50 lei
SUMA MATERIALE = 201.60 + 49.00 + 10.50 = 261.10 lei

MATERIALE+REGIE = 261.10 × 1.4 = 365.54 lei
Manoperă = 0 ore × 75 = 0.00 lei
Transport = 0 km × 2 = 0.00 lei

Bază calcul PROFIT = 365.54 + 0 + 0 = 365.54 lei
PROFIT (30%) = 365.54 × 0.30 = 109.66 lei

Total fără TVA = 365.54 + 109.66 + 0 + 0 = 475.20 lei
TVA 21% = 475.20 × 0.21 = 99.79 lei
Total cu TVA (Total Final) = 475.20 + 99.79 = 574.99 lei

NOTĂ IMPORTANTĂ: Prezintă calculul în format tabel clar, cu toate liniile de calcul!

=== INSTRUCȚIUNI DE FORMATARE RĂSPUNS ===

Când prezinți oferta, folosește ÎNTOTDEAUNA următorul format cu emojii și structură clară:

# 📋 CALCUL OFERTĂ TERASĂ

## 📦 DETALIU CALCUL

### 1️⃣ MATERIALE FIZICE
| Material | Cantitate | Preț unitar | Total |
|----------|-----------|-------------|--------|
| FOLIE CRISTAL | 6 mp | 33.60 lei/mp | **201.60 lei** |
| FERMOAR | 1 buc | 49.00 lei/buc | **49.00 lei** |
| CARABINE | 5 buc | 2.10 lei/buc | **10.50 lei** |

**SUMA MATERIALE** = 261.10 lei

### 2️⃣ MATERIALE + REGIE
Formula: Suma Materiale × 1.4
**365.54 lei** ✓

### 3️⃣ PROFIT (30%)
Formula: (Materiale+Regie + Manoperă + Transport) × 0.30
Bază calcul profit: 365.54 + 0.00 + 0.00 = 365.54 lei
**109.66 lei** ✓

### 4️⃣ MANOPERĂ
**0 ore** × 75 lei/oră = **0.00 lei** ⚠️ **(valoare estimată - nu a fost specificată)**

### 5️⃣ DEPLASARE/TRANSPORT
**0 km** × 2 lei/km = **0.00 lei** ⚠️ **(valoare estimată - nu a fost specificată)**

---

## 📊 REZUMAT OFERTĂ

| Indicator | Valoare |
|-----------|---------|
| Materiale fizice | 261.10 lei |
| MATERIALE + REGIE | 365.54 lei |
| Manoperă | 0.00 lei |
| Transport | 0.00 lei |
| PROFIT (30%) | 109.66 lei |
| **TOTAL FĂRĂ TVA** | **475.20 lei** |
| TVA (21%) | 99.79 lei |
| 🎯 **TOTAL CU TVA** | **574.99 lei** |

---

**Prețul final pentru terasa solicitată este: 574.99 lei (cu TVA inclus)**

REGULI DE FORMATARE OBLIGATORII:
1. Folosește emojii pentru fiecare secțiune (📋 📦 💰 👷 🚚 📊 🎯)
2. Folosește tabele Markdown pentru materiale și rezumat
3. Marchează cu ⚠️ (emoji roșu de avertizare) și **bold roșu** valorile estimate sau lipsă (ore manoperă, km transport)
4. Folosește **bold** pentru toate valorile importante
5. Folosește ✓ (checkmark) pentru confirmarea calculelor corecte
6. Adaugă linii separatoare (---) între secțiuni
7. Finalizează cu fraza: "Prețul final pentru terasa solicitată este: X lei (cu TVA inclus)"

EOT;
    }

    /**
     * Update a specific terase price
     */
    public function updateTerasePrice(int $index, array $newData): array
    {
        $prices = self::TERASE_PRICES;

        if (!isset($prices[$index])) {
            throw new Exception('Indexul produsului nu există.');
        }

        $prices[$index] = array_merge($prices[$index], $newData);

        // Note: Since prices are hardcoded, changes are temporary
        // In production, you would save this to a database
        return $prices[$index];
    }

    /**
     * Read Excel file and return data as array
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public function readExcel(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception('Fișierul Excel nu există.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Validate that we have at least a header row
            if (empty($rows)) {
                throw new Exception('Fișierul Excel este gol.');
            }

            // Get header row
            $header = $rows[0];

            // Validate that we have the required 7 columns
            if (count($header) < 7) {
                throw new Exception('Fișierul Excel nu are cele 7 coloane necesare (Vanzator, DataDoc, Client, Articol, UM, Cantitate, Pret fara tva).');
            }

            // Remove header row
            array_shift($rows);

            // Format data as associative array
            $data = [];
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $data[] = [
                    'vanzator' => $row[0] ?? '',
                    'data_doc' => $row[1] ?? '',
                    'client' => $row[2] ?? '',
                    'articol' => $row[3] ?? '',
                    'um' => $row[4] ?? '',
                    'cantitate' => $row[5] ?? 0,
                    'pret_fara_tva' => $row[6] ?? 0.0,
                ];
            }

            return $data;
        } catch (Exception $e) {
            throw new Exception('Eroare la citirea fișierului Excel: ' . $e->getMessage());
        }
    }

    /**
     * Format Excel data as plain text table for Claude API
     *
     * @param array $data
     * @return string
     */
    public function formatAsText(array $data): string
    {
        $text = "Vanzator\tDataDoc\tClient\tArticol\tUM\tCantitate\tPret fara TVA\n";

        foreach ($data as $row) {
            $text .= sprintf(
                "%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
                $row['vanzator'],
                $row['data_doc'],
                $row['client'],
                $row['articol'],
                $row['um'],
                $row['cantitate'],
                $row['pret_fara_tva']
            );
        }

        return $text;
    }

    /**
     * Update a specific row in Excel file
     *
     * @param string $filePath
     * @param int $rowIndex (0-based, excluding header)
     * @param array $newData
     * @return void
     * @throws Exception
     */
    public function updateRow(string $filePath, int $rowIndex, array $newData): void
    {
        if (!file_exists($filePath)) {
            throw new Exception('Fișierul Excel nu există.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Row index in Excel (header is row 1, data starts at row 2)
            $excelRowIndex = $rowIndex + 2;

            // Update cells
            $worksheet->setCellValue('A' . $excelRowIndex, $newData['vanzator'] ?? '');
            $worksheet->setCellValue('B' . $excelRowIndex, $newData['data_doc'] ?? '');
            $worksheet->setCellValue('C' . $excelRowIndex, $newData['client'] ?? '');
            $worksheet->setCellValue('D' . $excelRowIndex, $newData['articol'] ?? '');
            $worksheet->setCellValue('E' . $excelRowIndex, $newData['um'] ?? '');
            $worksheet->setCellValue('F' . $excelRowIndex, $newData['cantitate'] ?? 0);
            $worksheet->setCellValue('G' . $excelRowIndex, $newData['pret_fara_tva'] ?? 0.0);

            // Save the file
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);
        } catch (Exception $e) {
            throw new Exception('Eroare la actualizarea fișierului Excel: ' . $e->getMessage());
        }
    }

    /**
     * Read price list Excel (3 columns: denumire, um, pret_cu_tva)
     */
    public function readPriceList(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception('Fișierul Excel nu există.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                throw new Exception('Fișierul Excel este gol.');
            }

            // Remove header row
            array_shift($rows);

            $data = [];
            foreach ($rows as $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $data[] = [
                    'denumire' => $row[0] ?? '',
                    'um' => $row[1] ?? '',
                    'pret_cu_tva' => $row[2] ?? 0,
                ];
            }

            return $data;
        } catch (Exception $e) {
            throw new Exception('Eroare la citirea listei de prețuri: ' . $e->getMessage());
        }
    }

    /**
     * Update a row in the price list Excel
     */
    public function updatePriceRow(string $filePath, int $rowIndex, array $newData): void
    {
        if (!file_exists($filePath)) {
            throw new Exception('Fișierul Excel nu există.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $excelRowIndex = $rowIndex + 2; // +1 for header, +1 for 1-based

            $worksheet->setCellValue('A' . $excelRowIndex, $newData['denumire'] ?? '');
            $worksheet->setCellValue('B' . $excelRowIndex, $newData['um'] ?? '');
            $worksheet->setCellValue('C' . $excelRowIndex, $newData['pret_cu_tva'] ?? 0);

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);
        } catch (Exception $e) {
            throw new Exception('Eroare la actualizarea prețului: ' . $e->getMessage());
        }
    }

    /**
     * Delete a row from the price list Excel
     */
    public function deletePriceRow(string $filePath, int $rowIndex): void
    {
        if (!file_exists($filePath)) {
            throw new Exception('Fișierul Excel nu există.');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $excelRowIndex = $rowIndex + 2; // +1 for header, +1 for 1-based
            $worksheet->removeRow($excelRowIndex);

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);
        } catch (Exception $e) {
            throw new Exception('Eroare la ștergerea rândului: ' . $e->getMessage());
        }
    }
}
