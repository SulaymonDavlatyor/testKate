<?php

namespace App\Dto;

class TournamentDTO
{
    public string $name;
    public array $teams;

    public function __construct(string $name, array $teams)
    {
        $this->name = $name;
        $this->teams = $teams;
    }
}
