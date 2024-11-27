<?php

namespace App\Entity;

use App\Repository\MissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MissionRepository::class)]
class Mission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $difficulty = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column]
    private ?int $timeLimit = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'pending';

    #[ORM\ManyToOne(inversedBy: 'missions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?SuperHero $superHero = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $result = null;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\ManyToMany(targetEntity: Power::class)]
    private Collection $requiredPowers;

    public function __construct()
    {
        $this->requiredPowers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(int $difficulty): static
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;
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

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(int $timeLimit): static
    {
        $this->timeLimit = $timeLimit;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getSuperHero(): ?SuperHero
    {
        return $this->superHero;
    }

    public function setSuperHero(?SuperHero $superHero): static
    {
        $this->superHero = $superHero;
        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): static
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return Collection<int, Power>
     */
    public function getRequiredPowers(): Collection
    {
        return $this->requiredPowers;
    }

    public function addRequiredPower(Power $power): static
    {
        if (!$this->requiredPowers->contains($power)) {
            $this->requiredPowers->add($power);
        }

        return $this;
    }

    public function removeRequiredPower(Power $power): static
    {
        $this->requiredPowers->removeElement($power);
        return $this;
    }
}
