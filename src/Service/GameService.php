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
        $this->createPlayoffGames($tournament, $topTeams);
    }

    private function createNextPlayoffGames(Tournament $tournament)
    {
        $winners = $this->getPlayoffWinners($tournament, 'upper');
        $losers = $this->getPlayoffWinners($tournament, 'lower');

        if (count($winners) <= 1) {
            return;
        }

        $this->createPlayoffGames($tournament, $winners, 'upper');

        if (count($losers) > 1) {
            $this->createPlayoffGames($tournament, $losers, 'lower');
        }
    }

    private function getTopTeamsForPlayoff(Tournament $tournament): array
    {
        $teamsA = $this->getTeamsWithScores($tournament, GameType::DIVISION_A);
        $teamsB = $this->getTeamsWithScores($tournament, GameType::DIVISION_B);

        usort($teamsA, fn($a, $b) => $b['score'] <=> $a['score']);
        usort($teamsB, fn($a, $b) => $b['score'] <=> $a['score']);

        $topTeamsA = array_slice($teamsA, 0, 4);
        $topTeamsB = array_slice($teamsB, 0, 4);

        return array_merge($topTeamsA, $topTeamsB);
    }

    private function getTeamsWithScores(Tournament $tournament, string $divisionType): array
    {
        return $this->entityManager->getRepository(Game::class)
            ->createQueryBuilder('g')
            ->select('g.teamOneId as teamId, SUM(g.teamOneScore) as score')
            ->where('g.tournament = :tournament')
            ->andWhere('g.type = :type')
            ->setParameter('tournament', $tournament)
            ->setParameter('type', $divisionType)
            ->groupBy('g.teamOneId')
            ->getQuery()
            ->getResult();
    }

    private function getPlayoffWinners(Tournament $tournament, string $stage): array
    {
        $games = $this->entityManager->getRepository(Game::class)->findBy([
            'tournament' => $tournament,
            'type' => GameType::PLAYOFF,
            'stage' => $stage,
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

    private function createPlayoffGames(Tournament $tournament, array $teams, string $stage = 'upper')
    {
        usort($teams, fn($a, $b) => $a['score'] <=> $b['score']);
        
        $pairs = [
            [$teams[0]['teamId'], $teams[7]['teamId']],
            [$teams[1]['teamId'], $teams[6]['teamId']],
            [$teams[2]['teamId'], $teams[5]['teamId']],
            [$teams[3]['teamId'], $teams[4]['teamId']]
        ];

        foreach ($pairs as [$teamOneId, $teamTwoId]) {
            $game = new Game();
            $game->setName('Playoff Game');
            $game->setDate((new \DateTime())->format('Y-m-d H:i:s'));
            $game->setTournament($tournament);
            $game->setTeamOneId($teamOneId);
            $game->setTeamTwoId($teamTwoId);
            $game->setTeamOneScore(0);
            $game->setTeamTwoScore(0);
            $game->setType(GameType::PLAYOFF);
            $game->setStage($stage);
            $game->setFinished(false);

            $this->entityManager->persist($game);

            $event = new GameUpdatedEvent($game);
            $this->eventDispatcher->dispatch($event, GameUpdatedEvent::NAME);
        }

        $this->entityManager->flush();
    }
}
