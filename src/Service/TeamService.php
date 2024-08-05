<?php

namespace App\Service;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class TeamService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTeam(string $name): Team
    {
        $team = new Team();
        $team->setName($name);

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $team;
    }
}
