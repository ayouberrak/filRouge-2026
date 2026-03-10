<?php

namespace App\Services;

use App\Repository\UserRepo;

class UserServices
{
    protected $userRepo;

    public function __construct(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function create(array $data)
    {
        return $this->userRepo->create($data);
    }

    public function findByEmail(string $email)
    {
        return $this->userRepo->findByEmail($email);
    }

    public function update(int $id, array $data)
    {
        return $this->userRepo->update($id, $data);
    }

    public function getAll()
    {
        return $this->userRepo->getAll();
    }

    public function findById(int $id)
    {
        return $this->userRepo->findById($id);
    }
}