<?php

namespace Database\Factories;

use App\Modules\Report\Infrastructure\Models\DailyReportModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyReportFactory extends Factory
{
    protected $model = DailyReportModel::class;

    public function definition(): array
    {
        return [
            'formateur_id' => \App\Modules\User\Infrastructure\Models\UserModel::factory(),
            'classroom_id' => \App\Modules\Classroom\Infrastructure\Models\ClassroomModel::factory(),
            'date' => now()->toDateString(),
            'absences_count' => 0,
            'brief_status' => 'in_progress',
            'note' => $this->faker->sentence,
        ];
    }
}
