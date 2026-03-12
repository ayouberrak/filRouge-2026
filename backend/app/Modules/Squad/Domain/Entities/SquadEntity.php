<?php

namespace App\Modules\Squad\Domain\Entities;

class SquadEntity
{
    private ?int $id;
    private int $numero;
    private array $members;
    private string $dateDebut;
    private string $dateFin;

    public function __construct(
        ?int $id,
        int $numero,
        array $members,
        string $dateDebut,
        string $dateFin
    ) {
        $this->id = $id;
        $this->numero = $numero;
        $this->members = $members;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function getId(): ?int { return $this->id; }
    public function getNumero(): int { return $this->numero; }
    public function getMembers(): array { return $this->members; }
    public function getDateDebut(): string { return $this->dateDebut; }
    public function getDateFin(): string { return $this->dateFin; }
}