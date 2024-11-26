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

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'superHeroes')]
    private Collection $teams;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
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

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    // Méthode pour ajouter une équipe à un super-héros
    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addSuperHero($this); // Assurez-vous que l'ajout est également effectué côté Team
        }
        return $this;
    }

    // Méthode pour retirer une équipe d'un super-héros
    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            $team->removeSuperHero($this); // Retirer la relation côté Team également
        }
        return $this;
    }
}