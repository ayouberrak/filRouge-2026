<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Chat\Infrastructure\Models\ConversationModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use Illuminate\Support\Facades\DB;

class ChatSeeder extends Seeder
{
    public function run()
    {
        // 1. Create conversations for all classrooms
        $classrooms = ClassroomModel::all();
        foreach ($classrooms as $classroom) {
            $conversation = ConversationModel::firstOrCreate(
                ['type' => 'classroom', 'related_id' => $classroom->id],
                ['name' => 'Classe: ' . $classroom->name]
            );

            // Add formateur
            if ($classroom->formateur_id) {
                $this->addUserToConv($conversation->id, $classroom->formateur_id);
            }

            // Add all students in this classroom
            $students = UserModel::where('classroom_id', $classroom->id)->get();
            foreach ($students as $student) {
                $this->addUserToConv($conversation->id, $student->id);
            }
        }

        // 2. Create conversations for all squads
        $squads = SquadModel::all();
        foreach ($squads as $squad) {
            $conversation = ConversationModel::firstOrCreate(
                ['type' => 'squad', 'related_id' => $squad->id],
                ['name' => 'Squad: ' . $squad->name]
            );

            // Add all students in this squad
            $students = UserModel::where('squad_id', $squad->id)->get();
            foreach ($students as $student) {
                $this->addUserToConv($conversation->id, $student->id);
            }
            
            // Add formateur of the classroom to the squad chat too? Usually yes.
            $classroom = ClassroomModel::find($squad->classroom_id);
            if ($classroom && $classroom->formateur_id) {
                $this->addUserToConv($conversation->id, $classroom->formateur_id);
            }
        }
    }

    private function addUserToConv($convId, $userId)
    {
        $exists = DB::table('conversation_user')
            ->where('conversation_id', $convId)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            DB::table('conversation_user')->insert([
                'conversation_id' => $convId,
                'user_id' => $userId,
                'joined_at' => now()
            ]);
        }
    }
}
