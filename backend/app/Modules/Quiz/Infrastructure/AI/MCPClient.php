<?php

namespace App\Modules\Quiz\Infrastructure\AI;

use Illuminate\Support\Facades\Http;

class MCPClient
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
    }

    public function evaluateCode(string $consigne, string $code): array
    {
        if (empty($this->apiKey)) {
            return $this->errorResponse("Clé API manquante.");
        }

        $prompt = "
            Tu es un correcteur technique et pédagogique.

            Évalue le code de l'étudiant selon la consigne.

             IMPORTANT :
            - Sois juste mais bienveillant
            - Donne des points partiels si la notion de base est comprise
            - Ne donne 0% que si la réponse est totalement hors sujet ou vide
            - Encourage l’apprentissage avec un feedback constructif

            Réponds uniquement en JSON strict :
            {
            \"score\": 0-100,
            \"is_correct\": true/false,
            \"feedback\": \"court feedback pédagogique\"
            }

            Consigne : {$consigne}
            Code : {$code}
            ";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}"
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                "model" => "llama-3.1-8b-instant",
                "messages" => [
                    ["role" => "user", "content" => $prompt]
                ]
            ]);

            if ($response->failed()) {
                return $this->errorResponse("Erreur API (".$response->status().")");
            }

            $text = $response->json()['choices'][0]['message']['content'] ?? '';

            // // Extraire uniquement la partie JSON si l'IA a ajouté du texte autour
            // if (preg_match('/\{.*\}/s', $text, $matches)) {
            //     $text = $matches[0];
            // }

            $result = json_decode($text, true);

            if (!$result) {
                return $this->errorResponse("Réponse IA invalide.");
            }

            return $result;

        } catch (\Exception $e) {
            return $this->errorResponse("Erreur serveur : " . $e->getMessage());
        }
    }

    private function errorResponse(string $message): array
    {
        return [
            "score" => 0,
            "is_correct" => false,
            "feedback" => "error ". $message
        ];
    }
}