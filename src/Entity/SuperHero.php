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

    #[ORM\OneToMany(mappedBy: 'superHero', targetEntity: Mission::class)]
    private Collection $missions;

    #[ORM\ManyToMany(targetEntity: Power::class)]
    private Collection $powers;

    public function __construct()
    {
        $this->missions = new ArrayCollection();
        $this->powers = new ArrayCollection();
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
     * @return Collection<int, Mission>
     */
    public function getMissions(): Collection
    {
        return $this->missions;
    }

    public function addMission(Mission $mission): static
    {
        if (!$this->missions->contains($mission)) {
            $this->missions->add($mission);
            $mission->setSuperHero($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): static
    {
        if ($this->missions->removeElement($mission)) {
            if ($mission->getSuperHero() === $this) {
                $mission->setSuperHero(null);
            }
        }

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
            $this->powers->add($power);
        }

        return $this;
    }

    public function removePower(Power $power): self
    {
        $this->powers->removeElement($power);
        return $this;
    }
}