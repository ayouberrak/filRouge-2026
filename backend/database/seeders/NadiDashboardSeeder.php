<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use App\Modules\Chat\Infrastructure\Models\ConversationModel;
use App\Modules\Chat\Infrastructure\Models\MessageModel;

use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class NadiDashboardSeeder extends Seeder
{
    public function run()
    {
        // 1. Create the Formateur
        $formateur = UserModel::updateOrCreate(
            ['email' => 'formateur@yc.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => Hash::make('password'),
                'role' => 'formateur',
                'status' => 'active'
            ]
        );

        // 2. Create the Classroom
        $classroom = ClassroomModel::updateOrCreate(
            ['name' => 'Fullstack JS - Promo 2026'],
            ['formateur_id' => $formateur->id]
        );

        // 3. Create the main Student (Ayoub)
        $ayoub = UserModel::updateOrCreate(
            ['email' => 'ayoub@yc.com'],
            [
                'first_name' => 'Ayoub',
                'last_name' => 'Errak',
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'active',
                'classroom_id' => $classroom->id,
                'location' => 'Safi',
                'avatar_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?q=80&w=200&h=200&auto=format&fit=crop'
            ]
        );

        // 4. Create the Hall of Fame Students
        $topStudents = [
            [
                'first_name' => 'Youssef',
                'last_name' => 'El-Alami',
                'email' => 'youssef@yc.com',
                'location' => 'Safi',
                'avatar_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=200&h=200&auto=format&fit=crop'
            ],
            [
                'first_name' => 'Salma',
                'last_name' => 'Ben Slimane',
                'email' => 'salma@yc.com',
                'location' => 'Safi',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&h=200&auto=format&fit=crop'
            ],
            [
                'first_name' => 'Sami',
                'last_name' => 'Karim',
                'email' => 'sami@yc.com',
                'location' => 'Youssoufia',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&h=200&auto=format&fit=crop'
            ]
        ];

        $allStudentIds = [$ayoub->id];
        foreach ($topStudents as $s) {
            $st = UserModel::updateOrCreate(
                ['email' => $s['email']],
                array_merge($s, [
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'status' => 'active',
                    'classroom_id' => $classroom->id
                ])
            );
            $allStudentIds[] = $st->id;
        }

        // 5. Create Squads
        $this->seedSquads($classroom, $allStudentIds);

        // 6. Create Briefs
        $this->seedBriefs($formateur, $classroom);

        // 7. Create Chat Conversations
        $this->seedChatData($ayoub, $formateur, $classroom);

        // 8. Create Activities (Linked to ALL students)
        $this->seedActivities($formateur, $classroom, $allStudentIds);
    }

    private function seedBriefs($formateur, $classroom)
    {
        // 1. Architecture PHP & DDD
        $brief1 = BriefModel::updateOrCreate(
            ['title' => 'Architecture PHP & Principes DDD'],
            [
                'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=1200&auto=format&fit=crop',
                'description' => 'Maîtrisez la séparation des préoccupations dans un cadre industriel robuste.',
                'context' => "Migration vers une architecture en couches (Hexagonale/Clean).",
                'date_start' => Carbon::now()->subDays(3),
                'date_end' => Carbon::now()->addDays(2),
                'modality' => 'INDIVIDUAL',
                'status' => 'IN_PROGRESS',
                'tags' => ['PHP 8.4', 'DDD', 'Clean Arch'],
                'formateur_id' => $formateur->id
            ]
        );
        $brief1->classrooms()->sync([$classroom->id]);

        // 2. SQL Avancé
        $brief2 = BriefModel::updateOrCreate(
            ['title' => 'Base de données SQL Avancée'],
            [
                'image_url' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?q=80&w=1200&auto=format&fit=crop',
                'description' => 'Optimisation des performances et indexation poussée.',
                'context' => "Audit de schéma SQL et stratégie d'indexation.",
                'date_start' => Carbon::now()->subDays(20),
                'date_end' => Carbon::now()->subDays(10),
                'modality' => 'INDIVIDUAL',
                'status' => 'COMPLETED',
                'tags' => ['PostgreSQL', 'Performance'],
                'formateur_id' => $formateur->id
            ]
        );
        $brief2->classrooms()->sync([$classroom->id]);
    }

    private function seedSquads($classroom, $studentIds)
    {
        $squadNames = ['Alpha Squad', 'Beta Squad'];
        
        foreach ($squadNames as $index => $name) {
            $squad = SquadModel::updateOrCreate(
                ['name' => $name, 'classroom_id' => $classroom->id]
            );

            // Assign half students to each squad
            $slice = array_slice($studentIds, $index * 2, 2);
            UserModel::whereIn('id', $slice)->update(['squad_id' => $squad->id]);

            // Create Squad Chat
            $squadChat = ConversationModel::updateOrCreate(
                ['type' => 'squad', 'related_id' => $squad->id],
                ['name' => 'Squad: ' . $name]
            );
            
            // Add students and formateur to squad chat
            $participants = array_merge($slice, [$classroom->formateur_id]);
            $squadChat->users()->syncWithoutDetaching($participants);
        }
    }

    private function seedActivities($formateur, $classroom, $studentIds)
    {
        // Rich Activities
        $activities = [
            [
                'title' => 'API Security Best Practices',
                'description' => 'Maîtrisez les enjeux de la sécurité des API modernes. Les API sont vitales. La sécurité doit être intégrée dès la conception.',
                'activity_type' => 'veille',
                'duration' => '30 minutes',
            ],
            [
                'title' => 'Clean Architecture Workshop',
                'description' => 'Mise en pratique DDD/SOLID. Maintenance long terme et testabilité.',
                'activity_type' => 'workshop',
                'duration' => '4h',
            ]
        ];

        foreach ($activities as $a) {
            $activity = ActivityModel::updateOrCreate(
                ['title' => $a['title']],
                array_merge($a, [
                    'formateur_id' => $formateur->id,
                    'classroom_id' => $classroom->id,
                ])
            );

            // INSCRIPTION DE TOUS LES ÉTUDIANTS
            $activity->students()->syncWithoutDetaching($studentIds);
        }
    }

    private function seedChatData($ayoub, $formateur, $classroom)
    {
        $classChat = ConversationModel::updateOrCreate(
            ['name' => 'Classe - ' . $classroom->name],
            ['type' => 'classroom', 'related_id' => $classroom->id]
        );
        $classChat->users()->syncWithoutDetaching([$ayoub->id, $formateur->id]);
        
        MessageModel::create([
            'conversation_id' => $classChat->id,
            'sender_id' => $formateur->id,
            'content' => 'Bienvenue dans le chat de la classe !'
        ]);

        $dmChat = ConversationModel::create(['type' => 'individual']);
        $dmChat->users()->syncWithoutDetaching([$ayoub->id, $formateur->id]);
    }
}
