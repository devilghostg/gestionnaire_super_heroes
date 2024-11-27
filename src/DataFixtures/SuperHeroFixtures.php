<?php

namespace App\DataFixtures;

use App\Entity\Power;
use App\Entity\SuperHero;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SuperHeroFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des pouvoirs
        $powers = [
            'Super Force' => 'Force surhumaine permettant de soulever des objets très lourds',
            'Vol' => 'Capacité de voler et de se déplacer dans les airs',
            'Télékinésie' => 'Capacité de déplacer des objets par la pensée',
            'Invisibilité' => 'Pouvoir de devenir invisible à volonté',
            'Régénération' => 'Guérison rapide et régénération des tissus',
            'Contrôle Mental' => 'Capacité de lire et influencer les pensées',
            'Manipulation du Temps' => 'Contrôle sur le flux temporel',
            'Electrokinésie' => 'Manipulation de l\'électricité',
            'Pyrokinésie' => 'Manipulation du feu',
            'Cryokinésie' => 'Manipulation de la glace'
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
            ['Iron Knight', 'Tony Stone', 90, 'Dépendance à la technologie'],
            ['Spider-Person', 'Peter Johnson', 85, 'Responsabilités familiales'],
            ['Thunder God', 'Chris Blake', 95, 'Arrogance'],
            ['Green Monster', 'Bruce Green', 100, 'Colère incontrôlable'],
            ['Black Widow', 'Natasha Stone', 80, 'Passé trouble'],
            ['Captain Star', 'Steve Grant', 90, 'Trop idéaliste'],
            ['Scarlet Magician', 'Wanda Wilson', 95, 'Instabilité émotionnelle'],
            ['Quick Silver', 'Pietro Wilson', 85, 'Impatience'],
            ['Hawk Eye', 'Clint Stone', 75, 'Vision limitée'],
            ['Vision Prime', 'Victor Shade', 90, 'Dépendance à une pierre'],
            ['Black Leopard', 'T\'Chaka', 88, 'Traditions contraignantes'],
            ['Doctor Magic', 'Stephen Sharp', 92, 'Mains blessées'],
            ['Captain Galaxy', 'Carol Stars', 98, 'Amnésie partielle'],
            ['Ant Hero', 'Scott Lang', 82, 'Casier judiciaire'],
            ['Wasp Wing', 'Hope van Dyne', 84, 'Manque de confiance']
        ];

        foreach ($heroes as [$name, $alias, $energy, $weakness]) {
            $hero = new SuperHero();
            $hero->setName($name);
            $hero->setAlias($alias);
            $hero->setEnergy($energy);
            $hero->setWeakness($weakness);
            
            // Attribuer un pouvoir aléatoire
            $randomPower = $powerEntities[array_rand($powerEntities)];
            $hero->setPower($randomPower);
            
            $manager->persist($hero);
        }

        $manager->flush();
    }
}
