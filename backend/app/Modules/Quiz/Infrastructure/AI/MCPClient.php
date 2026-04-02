<?php

namespace App\Modules\Quiz\Infrastructure\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MCPClient
{
    private string $apiKey;
    private string $modelUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    public function evaluateCode(string $consigne, string $codeEtudiant): array
    {
        // 1. Fallback si pas de clé API
        if (empty($this->apiKey)) {
            return $this->smartSimulation($consigne, $codeEtudiant, "Clé API non configurée.");
        }

        $prompt = "Tu es un correcteur technique sévère et extrêmement pédagogue. Évalue le code suivant par rapport à la consigne. ".
                  "Tu NE DOIS RÉPONDRE QU'AVEC UN OBJET JSON STRICT respectant cette structure exacte : ".
                  "{\"score\": [entier entre 0 et 100], \"is_correct\": [booléen], \"feedback\": \"[Ton feedback au format Markdown explicatif]\"}. ".
                  "Ton feedback DOIT ÊTRE TRÈS DÉTAILLÉ. Il doit contenir :\n".
                  "1. Une analyse de ce qui est bien fait.\n".
                  "2. Une explication technique des erreurs ou des points d'amélioration.\n".
                  "3. Des conseils de bonnes pratiques.\n".
                  "Aucun texte avant ou après le JSON d'encadrement.\n\n".
                  "Consigne : {$consigne}\n".
                  "Code de l'étudiant :\n{$codeEtudiant}";

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.1, 
            ]
        ];

        try {
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->modelUrl}?key={$this->apiKey}", $payload);

            if ($response->failed()) {
                // Gestion des erreurs Google (ex: 429 Quota Exceeded)
                Log::warning("Gemini API Error: " . $response->status() . " - " . $response->body());
                return $this->smartSimulation($consigne, $codeEtudiant, "Erreur API (" . $response->status() . "). Évaluation hors-ligne activée.");
            }

            $body = $response->json();
            
            // Extraction du texte brut de Gemini
            $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Nettoyage radical (gemini adore mettre des blockquotes markdown)
            $cleanJson = trim(str_replace(['```json', '```'], '', $rawText));
            
            $result = json_decode($cleanJson, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($result['score'], $result['is_correct'], $result['feedback'])) {
                return $result;
            }

            throw new \Exception("Le format JSON de l'IA n'était pas valide.");

        } catch (\Exception $e) {
            Log::error("Gemini Parsing/Timeout Error: " . $e->getMessage());
            return $this->smartSimulation($consigne, $codeEtudiant, "Erreur d'analyse IA ou délai dépassé. Évaluation de secours activée.");
        }
    }

    /**
     * Fallback Robuste ("Nadi") si l'IA plante ou si quota dépassé
     */
    private function smartSimulation(string $consigne, string $codeEtudiant, string $reason): array
    {
        // 1. Vérification stricte de la syntaxe PHP s'il s'agit de code PHP
        if (str_contains($codeEtudiant, '<?php')) {
            $tempFile = tempnam(sys_get_temp_dir(), 'code_');
            file_put_contents($tempFile, $codeEtudiant);
            $output = [];
            $returnVar = 0;
            exec("php -l " . escapeshellarg($tempFile) . " 2>&1", $output, $returnVar);
            unlink($tempFile);

            if ($returnVar !== 0) {
                return [
                    'score' => 0,
                    'is_correct' => false,
                    'feedback' => "❌ **Erreur Fatale de Syntaxe**\nVotre code ne compile pas : `" . trim($output[0] ?? 'Erreur inconnue') . "`\n\n*(Évaluation automatique de secours déclenchée car " . strtolower($reason) . ")*"
                ];
            }
        }

        // 2. Analyse lexicale très basique si la syntaxe est valide
        $codeLower = strtolower($codeEtudiant);
        $score = 0;
        
        if (strlen(trim($codeEtudiant)) > 10) $score += 20; 
        if (str_contains($codeLower, 'try') && str_contains($codeLower, 'catch')) $score += 30; 
        if (str_contains($codeLower, 'pdo') || str_contains($codeLower, 'mysql')) $score += 30;
        if (str_contains($codeLower, 'class') || str_contains($codeLower, 'function')) $score += 20;
        
        if ($score > 100) $score = 100;

        return [
            'score' => $score,
            'is_correct' => $score >= 70,
            'feedback' => "⚠️ *Évaluation Automatique Sécurisée ({$reason})*\n\n" .
                          "### Rapport Provisoire\n" .
                          "- **Longueur du code** : " . (strlen($codeEtudiant) > 10 ? 'Acceptable ✅' : 'Faible ❌') . "\n" .
                          "- **Score attribué algorithmiquement** : **{$score}/100**."
        ];
    }
}
