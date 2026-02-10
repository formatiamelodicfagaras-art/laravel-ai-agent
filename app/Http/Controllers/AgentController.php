<?php

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Exception;

class AgentController extends Controller
{
    private ClaudeService $claudeService;
    private ExcelService $excelService;

    public function __construct(ClaudeService $claudeService, ExcelService $excelService)
    {
        $this->claudeService = $claudeService;
        $this->excelService = $excelService;
    }

    /**
     * Show agent page with chat history
     */
    public function index()
    {
        $chatHistory = session('chat_history', []);

        return view('agent', [
            'chatHistory' => $chatHistory
        ]);
    }

    /**
     * Process question and get response from Claude
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ], [
            'question.required' => 'Vă rugăm să introduceți o întrebare.',
            'question.max' => 'Întrebarea nu poate depăși 1000 de caractere.',
        ]);

        $question = $request->input('question');

        // Define database files
        $databases = [
            'contabilitate' => storage_path('app/uploads/data.xlsx'),
            'preturi_2026' => storage_path('app/uploads/preturi_2026.xlsx'),
        ];

        // Check if at least one database exists
        $hasData = false;
        foreach ($databases as $db) {
            if (file_exists($db)) {
                $hasData = true;
                break;
            }
        }

        if (!$hasData) {
            return back()->withErrors(['question' => 'Nu există nicio bază de date încărcată. Încărcați cel puțin un fișier Excel.']);
        }

        try {
            // Read and combine all available databases
            $combinedText = '';

            // Extrage cuvinte cheie din întrebare (minim 3 caractere)
            $questionLower = mb_strtolower($question);
            // Elimină punctuația și split pe spații
            $questionClean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $questionLower);
            $words = preg_split('/\s+/', $questionClean);

            // Stop words românești - cuvinte comune de ignorat
            $stopWords = [
                // Cuvinte comune
                'care', 'sunt', 'este', 'fost', 'pentru', 'prin', 'din', 'cele', 'mai',
                'cat', 'cate', 'cati', 'cum', 'unde', 'cand', 'daca', 'sau', 'ori',
                'asa', 'astfel', 'chiar', 'doar', 'numai', 'foarte', 'mult', 'putini',
                // Verbe comune și forme verbale (toate formele)
                'cauta', 'gaseste', 'arata', 'afiseaza', 'spune', 'zice', 'vreau', 'vrea',
                'vandut', 'vandute', 'vanduta', 'vanduti', 'vanzare', 'vanzari', 'cumparat', 'livrat', 'facturat',
                'avea', 'avem', 'aveam', 'avut', 'are', 'aveti', 'fost', 'era', 'eram',
                'facut', 'face', 'fac', 'faceti', 'trimis', 'primit', 'platit',
                // Cuvinte nedeterminate/cantitative
                'niste', 'ceva', 'cateva', 'cativa', 'multi', 'multe', 'putine', 'putini',
                'unele', 'unii', 'anumite', 'anumiti', 'orice', 'oricare', 'fiecare',
                // Cuvinte legate de date/coloane (NU sunt nume de produse/clienți)
                'pret', 'pretul', 'preturi', 'cantitate', 'cantitatea', 'cantitati',
                'data', 'datele', 'document', 'factura', 'facturi',
                'lei', 'buc', 'bucati', 'unitar', 'total', 'valoare', 'valoarea',
                // Pronume și articole
                'toate', 'toti', 'toata', 'tot', 'acest', 'aceasta', 'acesta', 'aceste', 'acestea',
                'lui', 'lor', 'meu', 'mea', 'tau', 'noastra', 'voastra',
                'cel', 'cea', 'cei', 'cele', 'unui', 'unei',
                // Engleza
                'the', 'and', 'for', 'with', 'have', 'has', 'been', 'what', 'how', 'much'
            ];

            $keywords = array_filter($words, function($w) use ($stopWords) {
                return mb_strlen($w) >= 3 && !in_array($w, $stopWords);
            });

            // Contabilitate database - căutare inteligentă
            if (file_exists($databases['contabilitate'])) {
                $data = $this->excelService->readExcel($databases['contabilitate']);
                $totalRows = count($data);

                // Căutare cu OR + scoring - rândurile cu mai multe potriviri vor fi primele
                $scoredData = [];
                foreach ($data as $index => $row) {
                    $clientText = mb_strtolower($row['client'] ?? '');
                    $articolText = mb_strtolower($row['articol'] ?? '');
                    $rowText = $clientText . ' ' . $articolText;

                    $score = 0;
                    foreach ($keywords as $keyword) {
                        $keywordFound = false;

                        // Căutare exactă în client sau articol
                        if (mb_strpos($rowText, $keyword) !== false) {
                            $keywordFound = true;
                            $score += 2; // Potrivire exactă = 2 puncte
                        }

                        // Căutare fuzzy pentru articol (plural/singular)
                        if (!$keywordFound && mb_strlen($keyword) >= 4) {
                            $allWords = preg_split('/[\s,]+/', $rowText);
                            foreach ($allWords as $word) {
                                if (mb_strlen($word) >= 3) {
                                    if (mb_strpos($keyword, $word) !== false ||
                                        mb_strpos($word, $keyword) !== false) {
                                        $score += 1; // Potrivire fuzzy = 1 punct
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if ($score > 0) {
                        $scoredData[] = ['row' => $row, 'score' => $score];
                    }
                }

                // Sortăm după scor (cele mai relevante primele)
                usort($scoredData, fn($a, $b) => $b['score'] - $a['score']);

                // Extragem doar rândurile
                $filteredData = array_map(fn($item) => $item['row'], $scoredData);

                $combinedText .= "=== BAZA DE DATE CONTABILITATE ===\n";
                $combinedText .= "Total înregistrări în bază: {$totalRows}\n";

                if (count($filteredData) > 0) {
                    // Limităm la 150 rezultate (sortate după relevanță)
                    $limitedData = array_slice($filteredData, 0, 150);
                    $combinedText .= "Rezultate găsite pentru '" . implode(', ', $keywords) . "': " . count($filteredData) . " (sortate după relevanță)\n";
                    $combinedText .= $this->excelService->formatAsText($limitedData);
                    if (count($filteredData) > 150) {
                        $combinedText .= "(afișate primele 150 cele mai relevante din " . count($filteredData) . ")\n";
                    }
                } else {
                    // Dacă nu găsim nimic, afișăm primele 15 ca exemplu
                    $combinedText .= "Nu s-au găsit rezultate pentru: " . implode(', ', $keywords) . "\n";
                    $combinedText .= "Primele 15 înregistrări din bază:\n";
                    $limitedData = array_slice($data, 0, 15);
                    $combinedText .= $this->excelService->formatAsText($limitedData);
                }
                $combinedText .= "\n";
            }

            // Prețuri 2026 database - căutare inteligentă
            if (file_exists($databases['preturi_2026'])) {
                $data = $this->excelService->readPriceList($databases['preturi_2026']);
                $totalRows = count($data);

                // Căutare în baza de date după cuvinte cheie
                $filteredData = [];
                foreach ($data as $row) {
                    $rowText = mb_strtolower($row['denumire'] . ' ' . $row['um']);
                    foreach ($keywords as $keyword) {
                        if (mb_strpos($rowText, $keyword) !== false) {
                            $filteredData[] = $row;
                            break;
                        }
                    }
                }

                $combinedText .= "=== LISTĂ PREȚURI 2026 (PREȚURI CU TVA INCLUS) ===\n";
                $combinedText .= "IMPORTANT: Prețurile din această listă sunt DEJA cu TVA inclus! NU adăuga TVA din nou!\n";
                $combinedText .= "Total produse: {$totalRows}\n";
                $combinedText .= "Denumire\tU.M.\tPreț cu TVA (lei)\n";

                $displayData = count($filteredData) > 0 ? $filteredData : array_slice($data, 0, 15);
                if (count($filteredData) > 0) {
                    $combinedText .= "Rezultate găsite: " . count($filteredData) . "\n";
                } else {
                    $combinedText .= "Nu s-au găsit rezultate specifice. Primele 15 produse:\n";
                }

                foreach ($displayData as $row) {
                    $combinedText .= sprintf("%s\t%s\t%s lei\n", $row['denumire'], $row['um'], $row['pret_cu_tva']);
                }
                $combinedText .= "\n";
            }

            // Terase prices (hardcoded)
            $teraseData = $this->excelService->getTerasePrices();
            $combinedText .= "=== LISTĂ PREȚURI TERASE (pentru calcule) ===\n";
            $combinedText .= "Produs\tU.M.\tPreț Achiziție\tPreț Vânzare\tMarjă %\n";
            foreach ($teraseData as $row) {
                $combinedText .= sprintf(
                    "%s\t%s\t%.2f lei\t%.2f lei\t%d%%\n",
                    $row['produs'],
                    $row['um'],
                    $row['pret_achizitie'],
                    $row['pret_vanzare'],
                    $row['marja']
                );
            }
            $combinedText .= "\n";

            // Add calculation instructions
            $combinedText .= $this->excelService->getTeraseCalculationInstructions();
            $combinedText .= "\n";

            // Get current chat history for conversation context
            $chatHistory = session('chat_history', []);

            // Get response from Claude with all databases and conversation history
            $answer = $this->claudeService->askQuestion($question, $combinedText, $chatHistory);

            // Add new Q&A pair
            $chatHistory[] = [
                'question' => $question,
                'answer' => $answer,
                'timestamp' => now()->format('H:i')
            ];

            // Keep only last 10 exchanges
            if (count($chatHistory) > 10) {
                $chatHistory = array_slice($chatHistory, -10);
            }

            // Save to session
            session(['chat_history' => $chatHistory]);

            return redirect('/agent');
        } catch (Exception $e) {
            return back()->withErrors(['question' => 'Eroare: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear chat history
     */
    public function clear()
    {
        session()->forget('chat_history');
        return redirect('/agent')->with('success', 'Istoricul conversației a fost șters.');
    }
}
