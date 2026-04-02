<?php

namespace Database\Factories;

use App\Modules\Brief\Infrastructure\Models\BriefModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BriefFactory extends Factory
{
    protected $model = BriefModel::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'date_start' => now(),
            'date_end' => now()->addDays(7),
            'difficulty' => 'easy',
            'status' => 'active',
            'formateur_id' => \App\Modules\User\Infrastructure\Models\UserModel::factory(),
        ];
    }
}
