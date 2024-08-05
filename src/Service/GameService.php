<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\GameType;
use App\Event\GameUpdatedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GameService
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function updateGame(Game $game, bool $finished, int $teamOneScore, int $teamTwoScore): Game
    {
        $game->setFinished($finished);
        $game->setTeamOneScore($teamOneScore);
        $game->setTeamTwoScore($teamTwoScore);

        $this->entityManager->flush();

        $event = new GameUpdatedEvent($game);
        $this->eventDispatcher->dispatch($event, GameUpdatedEvent::NAME);

        if ($finished) {
            if ($this->isLastDivisionGame($game)) {
                $this->startPlayOff($game->getTournament());
            } elseif ($this->isLastPlayoffGame($game)) {
                $this->createNextPlayoffGames($game->getTournament());
            }
        }

        return $game;
    }

    private function isLastDivisionGame(Game $game): bool
    {
        $unfinishedGames = $this->entityManager->getRepository(Game::class)->findBy([
            'tournament' => $game->getTournament(),
            'type' => [$game->getType()],
            'finished' => false,
        ]);

        return count($unfinishedGames) === 0;
    }

    private function isLastPlayoffGame(Game $game): bool
    {
        $unfinishedPlayoffGames = $this->entityManager->getRepository(Game::class)->findBy([
            'tournament' => $game->getTournament(),
            'type' => GameType::PLAYOFF,
            'finished' => false,
        ]);

        return count($unfinishedPlayoffGames) === 0;
    }

    private function startPlayOff(Tournament $tournament)
    {
        $topTeams = $this->getTopTeamsForPlayoff($tournament);
        $this->createGames($tournament, $topTeams, 'Playoff Game', GameType::PLAYOFF);
    }

    private function createNextPlayoffGames(Tournament $tournament)
    {
        $winners = $this->getPlayoffWinners($tournament);

        if (count($winners) <= 1) {
            return;
        }

        $this->createGames($tournament, $winners, 'Playoff Game', GameType::PLAYOFF);
    }

    private function getTopTeamsForPlayoff(Tournament $tournament): array
    {
        return [];
    }

    private function getPlayoffWinners(Tournament $tournament): array
    {
        $games = $this->entityManager->getRepository(Game::class)->findBy([
            'tournament' => $tournament,
            'type' => GameType::PLAYOFF,
            'finished' => true,
        ]);

        $winners = [];
        foreach ($games as $game) {
            if ($game->getTeamOneScore() > $game->getTeamTwoScore()) {
                $winners[] = $game->getTeamOneId();
            } else {
                $winners[] = $game->getTeamTwoId();
            }
        }

        return $winners;
    }

    private function createGames(Tournament $tournament, array $teams, string $gameName, string $type)
    {
        for ($i = 0; $i < count($teams); $i += 2) {
            if (isset($teams[$i + 1])) {
                $game = new Game();
                $game->setName($gameName);
                $game->setDate((new \DateTime())->format('Y-m-d H:i:s'));
                $game->setTournament($tournament);
                $game->setTeamOneId($teams[$i]);
                $game->setTeamTwoId($teams[$i + 1]);
                $game->setTeamOneScore(0);
                $game->setTeamTwoScore(0);
                $game->setType($type);
                $game->setFinished(false);

                $this->entityManager->persist($game);

                $event = new GameUpdatedEvent($game);
                $this->eventDispatcher->dispatch($event, GameUpdatedEvent::NAME);
            }
        }

        $this->entityManager->flush();
    }
}
