<?php

namespace App\Event;

use App\Entity\Game;
use Symfony\Contracts\EventDispatcher\Event;

class GameUpdatedEvent extends Event
{
    public const NAME = 'game.updated';

    protected $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}
