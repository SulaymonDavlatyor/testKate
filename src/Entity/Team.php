<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $Tournament = null;

    #[ORM\ManyToMany(targetEntity: tournament::class, inversedBy: 'teams')]
    private Collection $tournament;

    public function __construct()
    {
        $this->tournament = new ArrayCollection();
    }

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

    public function getTournament(): ?string
    {
        return $this->Tournament;
    }

    public function setTournament(string $Tournament): static
    {
        $this->Tournament = $Tournament;

        return $this;
    }

    public function addTournament(tournament $tournament): static
    {
        if (!$this->tournament->contains($tournament)) {
            $this->tournament->add($tournament);
        }

        return $this;
    }

    public function removeTournament(tournament $tournament): static
    {
        $this->tournament->removeElement($tournament);

        return $this;
    }
}
