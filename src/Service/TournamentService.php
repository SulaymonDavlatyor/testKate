<?php

namespace App\Service;

use App\DTO\TournamentDTO;
use App\Entity\Tournament;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\GameType;

class TournamentService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTournament(TournamentDTO $tournamentDTO): Tournament
    {
        $tournament = new Tournament();
        $tournament->setName($tournamentDTO->name);

        $teams = $this->entityManager->getRepository(Team::class)->findBy(['id' => $tournamentDTO->teams]);

        foreach ($teams as $team) {
            $tournament->addTeam($team);
        }

        $this->entityManager->persist($tournament);
        $this->entityManager->flush();

        $divisionA = array_slice($tournamentDTO->teams, 0, count($tournamentDTO->teams) / 2);
        $divisionB = array_slice($tournamentDTO->teams, count($tournamentDTO->teams) / 2);

        $this->createGamesForDivision($tournament, $divisionA, 'Division A Game', GameType::DIVISION_A);
        $this->createGamesForDivision($tournament, $divisionB, 'Division B Game', GameType::DIVISION_B);

        return $tournament;
    }

    private function createGamesForDivision(Tournament $tournament, array $teams, string $divisionName, string $type)
    {
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                $game = new Game();
                $game->setName($divisionName);
                $game->setDate((new \DateTime())->format('Y-m-d H:i:s'));
                $game->setTournament($tournament);
                $game->setTeamOneId($teams[$i]);
                $game->setTeamTwoId($teams[$j]);
                $game->setTeamOneScore(0);
                $game->setTeamTwoScore(0);
                $game->setType($type);
                $game->setFinished(false);

                $this->entityManager->persist($game);
            }
        }
        $this->entityManager->flush();
    }
}
