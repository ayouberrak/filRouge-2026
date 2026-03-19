<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
use App\Modules\Livrable\Infrastructure\Models\ReponseLivrableModel;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;
use App\Modules\Marketplace\Infrastructure\Models\ProductModel;
use App\Modules\Marketplace\Infrastructure\Models\OrderModel;
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
                'total_points' => rand(100, 1500)
            ]);
        }

        // 5. Create Briefs
        $brief1 = BriefModel::create([
            'title' => 'React & DDD Architecture',
            'description' => 'Build a complex react app following DDD principles',
            'objectives' => 'React, DDD, Clean Code',
            'date_start' => now(),
            'date_end' => now()->addDays(7),
            'difficulty' => 'HARD',
            'modality' => 'INDIVIDUAL',
            'status' => 'IN_PROGRESS',
            'points' => 500,
            'formateur_id' => $formateur->id
        ]);
        $brief1->classrooms()->attach($classroom->id);

        $brief2 = BriefModel::create([
            'title' => 'Laravel Microservices',
            'description' => 'Implement microservices with Laravel',
            'objectives' => 'Laravel, RabbitMQ',
            'date_start' => now()->subDays(10),
            'date_end' => now()->subDays(3),
            'difficulty' => 'HARD', // EXPERT was not in enum ['EASY', 'MEDIUM', 'HARD']
            'modality' => 'GROUP', // SQUAD was not in enum ['INDIVIDUAL', 'GROUP']
            'status' => 'COMPLETED',
            'points' => 800,
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

        // 8. Create Products
        $p1 = ProductModel::create([
            'name' => 'Laptop Pro 16',
            'description' => 'MacBook Pro clone for high performance',
            'price' => 2000,
            'quantity' => 2,
            'image' => 'https://images.unsplash.com/photo-1517336714460-4c502b1c3984'
        ]);

        $p2 = ProductModel::create([
            'name' => 'Premium Hoodie YC',
            'description' => 'Stay comfortable while coding',
            'price' => 300,
            'quantity' => 10,
            'image' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7'
        ]);

        $p3 = ProductModel::create([
            'name' => 'Mechanical Keyboard',
            'description' => 'Blue switches for the best typing feel',
            'price' => 600,
            'quantity' => 5,
            'image' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae'
        ]);

        // 9. Create Orders
        OrderModel::create([
            'user_id' => $students[0]->id,
            'product_id' => $p2->id,
            'price_at_purchase' => 300,
            'status' => 'DELIVERED'
        ]);

        OrderModel::create([
            'user_id' => $students[1]->id,
            'product_id' => $p3->id,
            'price_at_purchase' => 600,
            'status' => 'PENDING'
        ]);
    }
}
