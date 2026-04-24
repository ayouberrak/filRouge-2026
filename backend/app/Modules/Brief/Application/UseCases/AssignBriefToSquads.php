<?php
 
 namespace App\Modules\Brief\Application\UseCases;
 
 use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
 use Exception;
 
 class AssignBriefToSquads
 {
     public function __construct(
         private BriefRepositoryInterface $briefRepository
     ) {}
 
     public function execute(int $briefId, array $squadIds): void
     {
         $brief = $this->briefRepository->findById($briefId);
         
         if (!$brief) {
             throw new Exception("Brief not found.");
         }
 
         $this->briefRepository->assignSquads($briefId, $squadIds);
         
         // On pourrait déclencher un événement ici aussi si besoin
     }
 }
