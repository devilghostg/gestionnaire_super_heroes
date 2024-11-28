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

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column]
    private ?int $timeLimit = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $status = 'pending';

    #[ORM\ManyToOne(inversedBy: 'missions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?SuperHero $superHero = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $result = null;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $progress = 0;

    #[ORM\ManyToMany(targetEntity: Power::class)]
    private Collection $requiredPowers;

    #[ORM\OneToMany(mappedBy: 'mission', targetEntity: MissionAssignment::class, orphanRemoval: true)]
    private Collection $assignments;

    public function __construct()
    {
        $this->requiredPowers = new ArrayCollection();
        $this->assignments = new ArrayCollection();
        $this->status = 'pending';
        $this->timeLimit = 120; // 2 minutes par défaut
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

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
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

    public function setStatus(string $status): self
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

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(?int $progress): self
    {
        $this->progress = $progress;
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

    /**
     * @return Collection<int, MissionAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(MissionAssignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setMission($this);
        }

        return $this;
    }

    public function removeAssignment(MissionAssignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getMission() === $this) {
                $assignment->setMission(null);
            }
        }

        return $this;
    }

    public function getActiveAssignments(): Collection
    {
        return $this->assignments->filter(function(MissionAssignment $assignment) {
            return $assignment->isIsActive();
        });
    }

    public function hasActiveHero(SuperHero $hero): bool
    {
        foreach ($this->getActiveAssignments() as $assignment) {
            if ($assignment->getHero() === $hero) {
                return true;
            }
        }
        return false;
    }

    public function getActiveHeroes(): array
    {
        return $this->assignments
            ->filter(fn(MissionAssignment $a) => $a->isIsActive())
            ->map(fn(MissionAssignment $a) => $a->getHero())
            ->toArray();
    }

    public function isHeroAssigned(SuperHero $hero): bool
    {
        return $this->assignments->exists(
            fn(int $key, MissionAssignment $a) => $a->getHero() === $hero && $a->isIsActive()
        );
    }
}
