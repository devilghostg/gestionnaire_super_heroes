<?php
// src/Entity/Team.php

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

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, SuperHero>
     */
    // Ajoutez 'inversedBy' pour indiquer la propriété de l'entité SuperHero qui gère la relation inverse
    #[ORM\ManyToMany(targetEntity: SuperHero::class, inversedBy: 'teams')]
    private Collection $superHeroes;

    public function __construct()
    {
        $this->superHeroes = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getSuperHeroes(): Collection
    {
        return $this->superHeroes;
    }

    public function addSuperHero(SuperHero $superHero): static
    {
        if (!$this->superHeroes->contains($superHero)) {
            $this->superHeroes->add($superHero);
            $superHero->addTeam($this); // Maintenir la relation bidirectionnelle
        }
        return $this;
    }

    public function removeSuperHero(SuperHero $superHero): static
    {
        if ($this->superHeroes->removeElement($superHero)) {
            $superHero->removeTeam($this); // Retirer la relation bidirectionnelle
        }
        return $this;
    }
}