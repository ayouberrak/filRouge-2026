<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserModel::create([
            'first_name' => 'Admin',
            'last_name' => 'Super',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        UserModel::create([
            'first_name' => 'John',
            'last_name' => 'Formateur',
            'email' => 'formateur@formateur.com',
            'password' => Hash::make('password123'),
            'role' => 'formateur',
            'status' => 'active',
            'speciality' => 'Développement Web',
        ]);

        UserModel::create([
            'first_name' => 'Alice',
            'last_name' => 'Student',
            'email' => 'student1@student.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'points' => 0,
        ]);

        UserModel::create([
            'first_name' => 'Bob',
            'last_name' => 'Student',
            'email' => 'student2@student.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
            'points' => 0,
        ]);
    }
}
