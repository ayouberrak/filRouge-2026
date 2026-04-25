<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
use App\Modules\Livrable\Infrastructure\Models\ReponseLivrableModel;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;

use Illuminate\Support\Facades\Hash;

class MockDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Admin
        UserModel::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@yc.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        // 2. Create Formateur
        $formateur = UserModel::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'formateur@yc.com',
            'password' => Hash::make('password'),
            'role' => 'formateur',
            'status' => 'active'
        ]);

        // 3. Create Classroom
        $classroom = ClassroomModel::create([
            'name' => 'Fullstack JS - Promo 2026',
            'formateur_id' => $formateur->id
        ]);

        // 4. Create Students
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = UserModel::create([
                'first_name' => 'Student',
                'last_name' => $i,
                'email' => "student$i@yc.com",
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'active',
                'classroom_id' => $classroom->id,
            ]);
        }

        // 5. Create Briefs
        $brief1 = BriefModel::create([
            'title' => 'React & DDD Architecture',
            'description' => 'Build a complex react app following DDD principles',
            'context' => 'Dans ce projet, vous allez plonger dans le monde de l\'architecture logicielle moderne. L\'objectif est de construire une application React robuste en isolant la logique métier (Domain) de l\'infrastructure. Vous apprendrez à gérer la complexité croissante des applications frontend sans sacrifier la maintenabilité.',
            'date_start' => now(),
            'date_end' => now()->addDays(7),
            'modality' => 'INDIVIDUAL',
            'status' => 'IN_PROGRESS',
            'formateur_id' => $formateur->id
        ]);
        $brief1->classrooms()->attach($classroom->id);

        $brief2 = BriefModel::create([
            'title' => 'Laravel Microservices',
            'description' => 'Implement microservices with Laravel',
            'context' => 'Le passage au micro-services est une étape cruciale pour les applications à haute disponibilité. Ce projet vous met dans la peau d\'un architecte Backend chargé de scinder un monolithe existant en services indépendants communiquant via des APIs REST et des files d\'attente. Un défi technique de haut vol !',
            'date_start' => now()->subDays(10),
            'date_end' => now()->subDays(3),
            'modality' => 'GROUP', // SQUAD was not in enum ['INDIVIDUAL', 'GROUP']
            'status' => 'COMPLETED',
            'formateur_id' => $formateur->id
        ]);
        $brief2->classrooms()->attach($classroom->id);

        // 6. Create Livrables
        foreach ($students as $student) {
            $livrable = LivrableModel::create([
                'brief_id' => $brief1->id,
                'student_id' => $student->id,
                'link' => 'https://github.com/student/react-project',
                'status' => 'SUBMITTED'
            ]);

            // Some validated
            if ($student->id % 2 === 0) {
                ReponseLivrableModel::create([
                    'livrable_id' => $livrable->id,
                    'formateur_id' => $formateur->id,
                    'status' => 'VALIDATED',
                    'message' => 'Excellent travail, les patterns DDD sont bien respectés.'
                ]);
                $livrable->update(['status' => 'VALIDATED']);
            }
        }

        // 7. Create Absences for today
        AbsenceModel::create([
            'student_id' => $students[0]->id,
            'date' => now()->toDateString(),
            'duration' => 8,
            'status' => 'justified',
            'justification_file' => 'medical.pdf'
        ]);

        AbsenceModel::create([
            'student_id' => $students[1]->id,
            'date' => now()->toDateString(),
            'duration' => 4,
            'status' => 'unjustified'
        ]);


    }
}
