<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\GameType;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tournament $tournament = null;

    #[ORM\Column]
    private ?int $teamOneId = null;

    #[ORM\Column]
    private ?int $teamTwoId = null;

    #[ORM\Column(nullable: true)]
    private ?int $teamOneScore = null;

    #[ORM\Column]
    private ?int $teamTwoScore = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(callback: [GameType::class, 'getValues'])]
    private ?string $type = null;

    #[ORM\Column(type: 'boolean')]
    private bool $finished = false; // Новое поле

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getTeamOneId(): ?int
    {
        return $this->teamOneId;
    }

    public function setTeamOneId(int $teamOneId): static
    {
        $this->teamOneId = $teamOneId;
        return $this;
    }

    public function getTeamTwoId(): ?int
    {
        return $this->teamTwoId;
    }

    public function setTeamTwoId(int $teamTwoId): static
    {
        $this->teamTwoId = $teamTwoId;
        return $this;
    }

    public function getTeamOneScore(): ?int
    {
        return $this->teamOneScore;
    }

    public function setTeamOneScore(?int $teamOneScore): static
    {
        $this->teamOneScore = $teamOneScore;
        return $this;
    }

    public function getTeamTwoScore(): ?int
    {
        return $this->teamTwoScore;
    }

    public function setTeamTwoScore(int $teamTwoScore): static
    {
        $this->teamTwoScore = $teamTwoScore;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): static
    {
        $this->finished = $finished;
        return $this;
    }
}
