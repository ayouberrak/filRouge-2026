<?php

namespace Database\Factories;

use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    protected $model = ClassroomModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'formateur_id' => \App\Modules\User\Infrastructure\Models\UserModel::factory(),
        ];
    }
}
