<?php

namespace App\Modules\User\Domain\Repositories;

interface UserRepositoryInterface
{
    public function findById(int $id);
    public function findByEmail(string $email);
    public function findAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;

    // Authentication specific methods
    public function verifyCredentials(string $email, string $password): bool;
    public function createToken(int $userId, string $tokenName): string;
    public function revokeToken(int $userId, string $tokenId): void;
}
