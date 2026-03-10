<?php

namespace App\Repository;

use App\Models\User;

class UserRepo
{
    public function create(array $data)
    {
        return User::create($data);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function update(int $id, array $data)
    {
        $user = User::find($id);
        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }

    public function getAll()
    {
        return User::all();
    }

    public function findById(int $id)
    {
        return User::find($id);
    }
    
}
