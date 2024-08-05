<?php

namespace App\Controller;

use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    private TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    #[Route('/team/new', name: 'team_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $name = $request->request->get('name');

        if (!$name) {
            return new Response('Team name is required', Response::HTTP_BAD_REQUEST);
        }
        $team = $this->teamService->createTeam($name);

        return new Response('Saved new team with id ' . $team->getId());
    }
}
