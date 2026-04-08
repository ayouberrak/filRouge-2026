<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\User\Infrastructure\Models\UserModel;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $formateur = UserModel::where('role', 'formateur')->first();
        if (!$formateur) return;

        $briefs = BriefModel::all();

        foreach ($briefs as $brief) {
            // Create a Quiz Session for each brief
            $sessionId = DB::table('quiz_sessions')->insertGetId([
                'brief_id' => $brief->id,
                'formateur_id' => $formateur->id,
                'timer_minutes' => 15,
                'passing_score' => 70,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add Questions based on brief title
            if (str_contains($brief->title, 'PHP')) {
                $this->seedPhpQuestions($sessionId);
            } elseif (str_contains($brief->title, 'SQL')) {
                $this->seedSqlQuestions($sessionId);
            } elseif (str_contains($brief->title, 'Flutter')) {
                $this->seedFlutterQuestions($sessionId);
            } else {
                $this->seedGeneralQuestions($sessionId);
            }
        }
    }

    private function seedPhpQuestions($sessionId)
    {
        DB::table('questions')->insert([
            [
                'quiz_session_id' => $sessionId,
                'type' => 'multiple_choice',
                'content' => "Mise en situation : Votre application Laravel devient lente lors de la récupération de 10 000 enregistrements. Quelle approche DDD utiliseriez-vous ?",
                'context_data' => json_encode(['options' => ['Utiliser un Repository avec pagination', 'Charger tout en cache Redis', 'Supprimer les relations Eloquent', 'Utiliser des Raw Queries uniquement']]),
                'correct_answer' => 'Utiliser un Repository avec pagination',
                'points' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'quiz_session_id' => $sessionId,
                'type' => 'code_simulation',
                'content' => "Dans une architecture hexagonale, où placez-vous la logique métier de validation des règles complexes ?",
                'context_data' => json_encode(['options' => ['Dans le Contrôleur', 'Dans l\'Entité du Domaine', 'Dans le Repository', 'Dans la Migration']]),
                'correct_answer' => 'Dans l\'Entité du Domaine',
                'points' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    private function seedSqlQuestions($sessionId)
    {
        DB::table('questions')->insert([
            [
                'quiz_session_id' => $sessionId,
                'type' => 'multiple_choice',
                'content' => "Vous devez optimiser une requête de recherche textuelle sur des millions de lignes. Quel type d'index PostgreSQL/MySQL privilégiez-vous ?",
                'context_data' => json_encode(['options' => ['B-Tree', 'Hash Index', 'FULLTEXT / GIN Index', 'Unique Index']]),
                'correct_answer' => 'FULLTEXT / GIN Index',
                'points' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    private function seedFlutterQuestions($sessionId)
    {
        DB::table('questions')->insert([
            [
                'quiz_session_id' => $sessionId,
                'type' => 'multiple_choice',
                'content' => "Votre application Flutter saccade lors du défilement d'une liste d'images. Quelle est la première optimisation à faire ?",
                'context_data' => json_encode(['options' => ['Utiliser ListView.builder', 'Augmenter la RAM du téléphone', 'Convertir toutes les images en PNG', 'Désactiver le Garbage Collector']]),
                'correct_answer' => 'Utiliser ListView.builder',
                'points' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    private function seedGeneralQuestions($sessionId)
    {
        DB::table('questions')->insert([
            [
                'quiz_session_id' => $sessionId,
                'type' => 'multiple_choice',
                'content' => "Quelle est la principale différence entre Git Merge et Git Rebase ?",
                'context_data' => json_encode(['options' => ['Merge crée un commit de fusion, Rebase réécrit l\'historique', 'Merge supprime le code, Rebase le garde', 'Merge est plus lent que Rebase', 'Il n\'y a aucune différence']]),
                'correct_answer' => 'Merge crée un commit de fusion, Rebase réécrit l\'historique',
                'points' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
