<?php

namespace App\Dto;

class GameDto
{
    public string $name;
    public string $date;
    public int $tournamentId;
    public int $teamOneId;
    public int $teamTwoId;
    public ?int $teamOneScore;
    public int $teamTwoScore;

    public function __construct(
        string $name,
        string $date,
        int $tournamentId,
        int $teamOneId,
        int $teamTwoId,
        ?int $teamOneScore,
        int $teamTwoScore
    ) {
        $this->name = $name;
        $this->date = $date;
        $this->tournamentId = $tournamentId;
        $this->teamOneId = $teamOneId;
        $this->teamTwoId = $teamTwoId;
        $this->teamOneScore = $teamOneScore;
        $this->teamTwoScore = $teamTwoScore;
    }
}
