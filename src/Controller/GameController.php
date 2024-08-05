<?php

namespace App\Controller;

use App\DTO\GameDTO;
use App\Entity\Game;
use App\Service\GameService;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    private GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    #[Route('/game', name: 'game_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $name = $request->request->get('name');
        $date = $request->request->get('date');
        $tournamentId = $request->request->get('tournament_id');
        $teamOneId = $request->request->get('team_one_id');
        $teamTwoId = $request->request->get('team_two_id');
        $teamOneScore = $request->request->get('team_one_score');
        $teamTwoScore = $request->request->get('team_two_score');

        if (!$name || !$date || !$tournamentId || !$teamOneId || !$teamTwoId || !$teamTwoScore) {
            return new Response('Invalid input', Response::HTTP_BAD_REQUEST);
        }

        $gameDTO = new GameDTO(
            $name,
            $date,
            $tournamentId,
            $teamOneId,
            $teamTwoId,
            $teamOneScore,
            $teamTwoScore
        );

        $game = $this->gameService->createGame($gameDTO);

        return new Response('Saved new game with id ' . $game->getId());
    }

    #[Route('/game/{id}/edit', name: 'game_edit', methods: ['POST'])]
    public function edit(Game $game, Request $request): Response
    {
        $name = $request->request->get('name');
        $date = $request->request->get('date');
        $tournamentId = $request->request->get('tournament_id');
        $teamOneId = $request->request->get('team_one_id');
        $teamTwoId = $request->request->get('team_two_id');
        $teamOneScore = $request->request->get('team_one_score');
        $teamTwoScore = $request->request->get('team_two_score');

        if (!$name || !$date || !$tournamentId || !$teamOneId || !$teamTwoId || !$teamTwoScore) {
            return new Response('Invalid input', Response::HTTP_BAD_REQUEST);
        }

        $gameDTO = new GameDTO(
            $name,
            $date,
            $tournamentId,
            $teamOneId,
            $teamTwoId,
            $teamOneScore,
            $teamTwoScore
        );

        $this->gameService->updateGame($game, $gameDTO);

        return new Response('Updated game with id ' . $game->getId());
    }
}
