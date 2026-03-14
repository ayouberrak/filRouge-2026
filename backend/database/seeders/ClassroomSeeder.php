<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\User\Infrastructure\Models\UserModel;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the formateur created in UserSeeder
        $formateur = UserModel::where('role', 'formateur')->first();

        if ($formateur) {
            $classroom = ClassroomModel::create([
                'name' => 'Web Development 2026',
                'formateur_id' => $formateur->id,
            ]);

            // Assign existing students to this classroom
            UserModel::where('role', 'student')->update([
                'classroom_id' => $classroom->id
            ]);
        }
    }
}
