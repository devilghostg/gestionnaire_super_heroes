<?php
// src/Entity/SuperHero.php

namespace App\Entity;

use App\Repository\SuperHeroRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperHeroRepository::class)]
class SuperHero
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $alias = null;

    #[ORM\ManyToOne(targetEntity: Power::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Power $power = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $weakness = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $is_active = false;

    #[ORM\Column]
    private ?int $energy = 100;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'superHeroes')]
    private ?Team $team = null;

    #[ORM\ManyToMany(targetEntity: Power::class)]
    private Collection $powers;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model3dPath = null;

    #[ORM\OneToOne(targetEntity: Mission::class)]
    #[ORM\JoinColumn(name: 'last_mission_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Mission $lastMission = null;

    #[ORM\OneToMany(mappedBy: 'hero', targetEntity: MissionAssignment::class, orphanRemoval: true)]
    private Collection $missionAssignments;

    public function __construct()
    {
        $this->missionAssignments = new ArrayCollection();
        $this->powers = new ArrayCollection();
        $this->energy = 100;
        $this->is_active = false;
    }

    // Getter et Setter pour l'ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et Setter pour le nom
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    // Getter et Setter pour l'alias
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): static
    {
        $this->alias = $alias;
        return $this;
    }

    // Getter et Setter pour le pouvoir
    public function getPower(): ?Power
    {
        return $this->power;
    }

    public function setPower(?Power $power): static
    {
        $this->power = $power;
        return $this;
    }

    // Méthode pour récupérer le nom du pouvoir
    public function getPowerName(): ?string
    {
        return $this->power ? $this->power->getName() : null;
    }

    // Getter et Setter pour la faiblesse
    public function getWeakness(): ?string
    {
        return $this->weakness;
    }

    public function setWeakness(string $weakness): static
    {
        $this->weakness = $weakness;
        return $this;
    }

    // Getter et Setter pour l'état actif/inactif
    public function isActive(): bool
    {
        return $this->is_active ?? false;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getEnergy(): ?int
    {
        return $this->energy;
    }

    public function setEnergy(int $energy): self
    {
        $this->energy = $energy;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return Collection<int, Power>
     */
    public function getPowers(): Collection
    {
        return $this->powers;
    }

    public function addPower(Power $power): self
    {
        if (!$this->powers->contains($power)) {
            $this->powers[] = $power;
        }
        return $this;
    }

    public function removePower(Power $power): self
    {
        $this->powers->removeElement($power);
        return $this;
    }

    public function getModel3dPath(): ?string
    {
        return $this->model3dPath;
    }

    public function setModel3dPath(?string $model3dPath): self
    {
        $this->model3dPath = $model3dPath;
        return $this;
    }

    public function getLastMission(): ?Mission
    {
        return $this->lastMission;
    }

    public function setLastMission(?Mission $mission): self
    {
        $this->lastMission = $mission;
        return $this;
    }

    /**
     * @return Collection<int, MissionAssignment>
     */
    public function getMissionAssignments(): Collection
    {
        return $this->missionAssignments;
    }

    public function addMissionAssignment(MissionAssignment $assignment): static
    {
        if (!$this->missionAssignments->contains($assignment)) {
            $this->missionAssignments->add($assignment);
            $assignment->setHero($this);
        }
        return $this;
    }

    public function removeMissionAssignment(MissionAssignment $assignment): static
    {
        if ($this->missionAssignments->removeElement($assignment)) {
            if ($assignment->getHero() === $this) {
                $assignment->setHero(null);
            }
        }
        return $this;
    }

    public function isAvailableForMission(): bool
    {
        return $this->isActive() && $this->getCurrentMissionAssignment() === null;
    }

    public function getCurrentMissionAssignment(): ?MissionAssignment
    {
        foreach ($this->missionAssignments as $assignment) {
            if ($assignment->isActive()) {
                return $assignment;
            }
        }
        return null;
    }

    public function getCurrentMission(): ?Mission
    {
        $assignment = $this->getCurrentMissionAssignment();
        return $assignment ? $assignment->getMission() : null;
    }
}