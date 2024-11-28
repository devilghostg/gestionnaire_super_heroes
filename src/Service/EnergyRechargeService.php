<?php

namespace App\Service;

use App\Entity\SuperHero;
use Doctrine\ORM\EntityManagerInterface;

class EnergyRechargeService
{
    private const RECHARGE_RATE = 10; // Taux de recharge en pourcentage par minute
    private const MAX_ENERGY = 100;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function rechargeEnergy(SuperHero $hero): float
    {
        // Si le héros est déjà au maximum d'énergie, ne rien faire
        if ($hero->getEnergy() >= self::MAX_ENERGY) {
            return $hero->getEnergy();
        }

        // Si l'énergie n'est pas définie, l'initialiser à 100
        if ($hero->getEnergy() === null) {
            $hero->setEnergy(self::MAX_ENERGY);
            $this->entityManager->persist($hero);
            $this->entityManager->flush();
            return self::MAX_ENERGY;
        }

        // Calculer le temps écoulé depuis la dernière mission
        $lastMission = $hero->getLastMission();
        if ($lastMission && ($lastMission->getCompletedAt() || $lastMission->getStartedAt())) {
            $lastMissionEnd = $lastMission->getCompletedAt() ?? $lastMission->getStartedAt();
            $minutesElapsed = (time() - $lastMissionEnd->getTimestamp()) / 60;
            
            // Calculer la nouvelle énergie
            $energyGained = $minutesElapsed * self::RECHARGE_RATE;
            $newEnergy = min(self::MAX_ENERGY, $hero->getEnergy() + $energyGained);
            
            // Mettre à jour l'énergie du héros
            $hero->setEnergy($newEnergy);
            $this->entityManager->persist($hero);
            $this->entityManager->flush();
            
            return $newEnergy;
        }

        // Si pas de dernière mission, garder l'énergie actuelle
        return $hero->getEnergy();
    }
}
