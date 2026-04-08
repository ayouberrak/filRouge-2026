<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use App\Modules\Chat\Infrastructure\Models\ConversationModel;
use App\Modules\Chat\Infrastructure\Models\MessageModel;
use App\Modules\Marketplace\Infrastructure\Models\ProductModel;
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
                'total_points' => 2540,
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
                'total_points' => 4850,
                'location' => 'Safi',
                'avatar_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=200&h=200&auto=format&fit=crop'
            ],
            [
                'first_name' => 'Salma',
                'last_name' => 'Ben Slimane',
                'email' => 'salma@yc.com',
                'total_points' => 4620,
                'location' => 'Safi',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&h=200&auto=format&fit=crop'
            ],
            [
                'first_name' => 'Sami',
                'last_name' => 'Karim',
                'email' => 'sami@yc.com',
                'total_points' => 4100,
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

        // 5. Create Briefs
        $this->seedBriefs($formateur, $classroom);

        // 6. Create Chat Conversations
        $this->seedChatData($ayoub, $formateur, $classroom);

        // 7. Create Marketplace Products & Activities (Linked to ALL students)
        $this->seedMarketplaceAndActivities($formateur, $classroom, $allStudentIds);
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
                'objectives' => ['Architecture en couches', 'SOLID', 'PHPUnit', 'SQL Optimization'],
                'pedagogical_modalities' => "Autonomie avec Peer Review quotidienne.",
                'evaluation_modalities' => "Revue individuelle (VIVA).",
                'date_start' => Carbon::now()->subDays(3),
                'date_end' => Carbon::now()->addDays(2),
                'difficulty' => 'HARD',
                'modality' => 'INDIVIDUAL',
                'status' => 'IN_PROGRESS',
                'points' => 1500,
                'tags' => ['PHP 8.4', 'DDD', 'Clean Arch'],
                'resources' => ['Doc Clean Arch', 'Boilerplate PHP'],
                'deliverables' => ['Repo Github', 'README.md'],
                'performance_criteria' => ['Respect SOLID', 'Test Coverage > 80%'],
                'target_competencies' => ['Design Arch', 'Backend Dev'],
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
                'objectives' => ['EXPLAIN analysis', 'B-Tree Indexing', 'ACID Transactions'],
                'pedagogical_modalities' => "Ateliers dirigés le matin.",
                'evaluation_modalities' => "Benchmark final (100 req/s).",
                'date_start' => Carbon::now()->subDays(20),
                'date_end' => Carbon::now()->subDays(10),
                'difficulty' => 'MEDIUM',
                'modality' => 'INDIVIDUAL',
                'status' => 'COMPLETED',
                'points' => 300,
                'tags' => ['PostgreSQL', 'Performance'],
                'resources' => ['Postgres Guide', 'Dataset SQL'],
                'deliverables' => ['Optimization Script', 'Benchmark Report'],
                'performance_criteria' => ['Latence < 50ms'],
                'target_competencies' => ['Database Admin'],
                'formateur_id' => $formateur->id
            ]
        );
        $brief2->classrooms()->sync([$classroom->id]);
    }

    private function seedMarketplaceAndActivities($formateur, $classroom, $studentIds)
    {
        // Products
        ProductModel::updateOrCreate(['name' => 'Avatar Skin : Cyberpunk UI'], [
            'description' => 'Thème exclusif néon.',
            'price' => 500,
            'quantity' => 10,
            'image' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=400&h=400&auto=format&fit=crop'
        ]);

        ProductModel::updateOrCreate(['name' => 'Badge : Master DDD'], [
            'description' => 'Affichez votre expertise DDD.',
            'price' => 1200,
            'quantity' => 5,
            'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&h=400&auto=format&fit=crop'
        ]);

        // Rich Activities
        $activities = [
            [
                'title' => 'API Security Best Practices',
                'description' => 'Maîtrisez les enjeux de la sécurité des API modernes.',
                'activity_type' => 'veille',
                'duration' => '30 minutes',
                'points' => 150,
                'objectives' => "- Enjeux sécurité API.\n- Vulnérabilités OWASP.\n- Bonnes pratiques Laravel Sanctum.",
                'context' => "Les API sont vitales. La sécurité doit être intégrée dès la conception.",
                'exploration_points' => "### Focus\n- Auth & Authz\n- Token Security\n- Rate Limiting",
                'work_rule' => 'En binôme',
                'resources' => "https://laravel.com/docs/sanctum\nhttps://owasp.org/www-project-api-security/"
            ],
            [
                'title' => 'Clean Architecture Workshop',
                'description' => 'Mise en pratique DDD/SOLID.',
                'activity_type' => 'workshop',
                'duration' => '4h',
                'points' => 300,
                'objectives' => "- Pattern Repository.\n- Domain vs Infrastructure.\n- UI decoupling.",
                'context' => "Maintenance long terme et testabilité.",
                'exploration_points' => "Entités, Value Objects, DTOs.",
                'work_rule' => 'Solo',
                'resources' => "https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html"
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
