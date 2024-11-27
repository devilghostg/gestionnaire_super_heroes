<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Repository\SuperHeroRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture implements DependentFixtureInterface
{
    private $superHeroRepository;

    public function __construct(SuperHeroRepository $superHeroRepository)
    {
        $this->superHeroRepository = $superHeroRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $teams = [
            [
                'name' => 'Les Vengeurs',
                'description' => 'Une équipe d\'élite de super-héros rassemblés pour défendre la Terre contre les menaces les plus dangereuses.',
                'heroes' => ['Iron Knight', 'Thunder God', 'Green Monster', 'Black Widow', 'Captain Star']
            ],
            [
                'name' => 'Les Défenseurs',
                'description' => 'Un groupe de héros solitaires unis pour protéger les innocents et combattre le crime organisé.',
                'heroes' => ['Doctor Magic', 'Spider-Person', 'Black Leopard']
            ],
            [
                'name' => 'Les Gardiens Cosmiques',
                'description' => 'Une équipe d\'aventuriers spatiaux qui protègent la galaxie des menaces extraterrestres.',
                'heroes' => ['Captain Galaxy', 'Vision Prime', 'Scarlet Magician', 'Quick Silver']
            ],
            [
                'name' => 'L\'Équipe Tactique',
                'description' => 'Une unité d\'élite spécialisée dans les opérations furtives et la reconnaissance.',
                'heroes' => ['Hawk Eye', 'Black Widow', 'Ant Hero', 'Wasp Wing']
            ],
            [
                'name' => 'Les Mystiques',
                'description' => 'Un cercle de héros maîtrisant les arts mystiques et protégeant notre dimension.',
                'heroes' => ['Doctor Magic', 'Scarlet Magician', 'Vision Prime']
            ]
        ];

        foreach ($teams as $teamData) {
            $team = new Team();
            $team->setName($teamData['name']);
            $team->setDescription($teamData['description']);

            // Ajouter les héros à l'équipe
            foreach ($teamData['heroes'] as $heroName) {
                $hero = $this->superHeroRepository->findOneBy(['name' => $heroName]);
                if ($hero) {
                    $team->addSuperHero($hero);
                }
            }

            $manager->persist($team);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SuperHeroFixtures::class,
        ];
    }
}
