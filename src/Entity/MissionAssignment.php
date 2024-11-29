<?php

namespace App\Entity;

use App\Repository\MissionAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MissionAssignmentRepository::class)]
class MissionAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Mission $mission = null;

    #[ORM\ManyToOne(inversedBy: 'missionAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SuperHero $hero = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $assignedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'float')]
    private float $energy = 100.0;

    public function __construct()
    {
        $this->assignedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(?Mission $mission): static
    {
        $this->mission = $mission;

        return $this;
    }

    public function getHero(): ?SuperHero
    {
        return $this->hero;
    }

    public function setHero(?SuperHero $hero): static
    {
        $this->hero = $hero;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAssignedAt(): ?\DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function setAssignedAt(\DateTimeImmutable $assignedAt): static
    {
        $this->assignedAt = $assignedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getEnergy(): float
    {
        return $this->energy;
    }

    public function setEnergy(float $energy): static
    {
        $this->energy = max(0, min(100, $energy));

        return $this;
    }

    public function decreaseEnergy(float $amount): static
    {
        $this->energy = max(0, $this->energy - $amount);

        return $this;
    }
}
