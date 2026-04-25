<?php

namespace Database\Factories;

use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = ActivityModel::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'type' => 'individual',
            'duration' => 60,
            'points' => 10,
            'formateur_id' => \App\Modules\User\Infrastructure\Models\UserModel::factory(),
            'classroom_id' => \App\Modules\Classroom\Infrastructure\Models\ClassroomModel::factory(),
        ];
    }
}
