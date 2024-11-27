<?php

namespace App\DataFixtures;

use App\Entity\Power;
use App\Entity\SuperHero;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des pouvoirs
        $powers = [
            'Super Force' => 'Force surhumaine permettant de soulever des objets très lourds',
            'Vol' => 'Capacité de voler et de se déplacer dans les airs',
            'Invisibilité' => 'Pouvoir de devenir invisible à volonté',
            'Télékinésie' => 'Capacité de déplacer des objets par la pensée',
            'Régénération' => 'Guérison rapide des blessures',
            'Vision Laser' => 'Projection de rayons d\'énergie par les yeux',
            'Contrôle Mental' => 'Capacité d\'influencer les pensées des autres',
            'Vitesse' => 'Super vitesse et réflexes surhumains'
        ];

        $powerEntities = [];
        foreach ($powers as $name => $description) {
            $power = new Power();
            $power->setName($name);
            $power->setDescription($description);
            $manager->persist($power);
            $powerEntities[] = $power;
        }

        // Création des super-héros
        $heroes = [
            [
                'name' => 'Superman',
                'alias' => 'Clark Kent',
                'powers' => ['Super Force', 'Vol', 'Vision Laser'],
                'energy' => 100,
                'weakness' => 'Kryptonite'
            ],
            [
                'name' => 'Spider-Man',
                'alias' => 'Peter Parker',
                'powers' => ['Super Force', 'Vitesse'],
                'energy' => 90,
                'weakness' => 'Responsabilité familiale'
            ],
            [
                'name' => 'Wonder Woman',
                'alias' => 'Diana Prince',
                'powers' => ['Super Force', 'Vol'],
                'energy' => 95,
                'weakness' => 'Liens attachés'
            ],
            [
                'name' => 'Jean Grey',
                'alias' => 'Phoenix',
                'powers' => ['Télékinésie', 'Contrôle Mental'],
                'energy' => 85,
                'weakness' => 'Instabilité émotionnelle'
            ],
            [
                'name' => 'Wolverine',
                'alias' => 'Logan',
                'powers' => ['Régénération', 'Super Force'],
                'energy' => 88,
                'weakness' => 'Adamantium'
            ]
        ];

        foreach ($heroes as $heroData) {
            $hero = new SuperHero();
            $hero->setName($heroData['name']);
            $hero->setAlias($heroData['alias']);
            $hero->setEnergy($heroData['energy']);
            $hero->setWeakness($heroData['weakness']);
            
            // Ajout des pouvoirs
            foreach ($powerEntities as $power) {
                if (in_array($power->getName(), $heroData['powers'])) {
                    $hero->addPower($power);
                }
            }
            
            $manager->persist($hero);
        }

        $manager->flush();
    }
}
