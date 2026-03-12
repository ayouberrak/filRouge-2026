<?php

namespace App\Modules\Deliverable\Domain\Entities;

class DeliverableEntity
{
    private ?int $id;
    private array $data;
    private string $livrableDate;

    public function __construct(
        ?int $id,
        array $data,
        string $livrableDate
    ) {
        $this->id = $id;
        $this->data = $data;
        $this->livrableDate = $livrableDate;
    }

    public function getId(): ?int { return $this->id; }
    public function getData(): array { return $this->data; }
    public function getLivrableDate(): string { return $this->livrableDate; }
}