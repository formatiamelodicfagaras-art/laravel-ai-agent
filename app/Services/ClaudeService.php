<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ClaudeService
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-5-20250929');
        $this->maxTokens = config('services.anthropic.max_tokens', 4096);
    }

    /**
     * Send a question to Claude API with Excel data context
     *
     * @param string $question
     * @param string $excelData
     * @return string
     * @throws Exception
     */
    public function askQuestion(string $question, string $excelData): string
    {
        $systemPrompt = "Tu ești un agent AI pentru Style Advertising. Ai acces la TREI baze de date: " .
            "1. BAZA DE DATE CONTABILITATE - date istorice de vânzări " .
            "2. LISTĂ PREȚURI 2026 - prețuri actualizate pentru calcule " .
            "3. BAZA DE DATE CALCULAȚIE TERASE - informații pentru calculații terase.\n\n" .
            "Datele sunt în format tabel cu coloanele: Vanzator, DataDoc, Client, Articol, UM, Cantitate, Pret fara TVA.\n\n" .
            "INSTRUCȚIUNI IMPORTANTE:\n" .
            "- Răspunde DOAR în limbă română\n" .
            "- Bazează-te STRICT pe datele furnizate - nu inventa informații\n" .
            "- AFIȘEAZĂ TOATE REZULTATELE găsite în date, nu doar primul! Dacă sunt 5 înregistrări, arată toate 5.\n" .
            "- Dacă ceva nu există în date, spune clar: 'Nu am informații în baza de date pentru aceasta.'\n" .
            "- Când ți se cere un calcul (ex: cost total, preț pentru X unități, suprafață × preț/mp), CALCULEAZĂ AUTOMAT:\n" .
            "  * Valoare totală = Cantitate × Preț unitar\n" .
            "  * Preț pentru cantitate custom = Cantitate cerută × Preț unitar\n" .
            "  * Pentru mp/metri liniari: aplică prețul corespunzător\n" .
            "- Identifică din ce bază de date provine informația când răspunzi\n" .
            "- Prezintă calculele clar, cu formula și rezultatul final\n" .
            "- La final, dacă sunt mai multe rezultate, calculează și TOTALUL GENERAL";

        $userPrompt = "Aici sunt datele:\n\n" . $excelData . "\n\nÎntrebarea:\n" . $question;

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $userPrompt
                    ]
                ]
            ]);

            if (!$response->successful()) {
                throw new Exception('Eroare la apelul API Claude: ' . $response->body());
            }

            $data = $response->json();

            // Extract the text content from Claude's response
            if (isset($data['content'][0]['text'])) {
                return $data['content'][0]['text'];
            }

            throw new Exception('Răspuns neașteptat de la API Claude.');
        } catch (Exception $e) {
            throw new Exception('Eroare la comunicarea cu Claude API: ' . $e->getMessage());
        }
    }
}