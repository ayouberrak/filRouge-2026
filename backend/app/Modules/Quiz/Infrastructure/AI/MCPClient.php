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
            - Sois juste mais pas trop sévère
            - Donne un score un peu généreux
            - Encourage l’apprentissage

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