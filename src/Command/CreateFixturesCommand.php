<?php

namespace App\Command;

use App\Entity\Power;
use App\Entity\SuperHero;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-fixtures',
    description: 'Crée des données fictives pour les super-héros et les pouvoirs',
)]
class CreateFixturesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Création des pouvoirs
        $powers = [
            ['name' => 'Super Force', 'description' => 'Force surhumaine permettant de soulever des objets très lourds'],
            ['name' => 'Vol', 'description' => 'Capacité de voler et de se déplacer dans les airs'],
            ['name' => 'Télékinésie', 'description' => 'Déplacer des objets par la pensée'],
            ['name' => 'Invisibilité', 'description' => 'Devenir invisible à volonté'],
            ['name' => 'Contrôle Mental', 'description' => 'Influencer les pensées des autres'],
            ['name' => 'Régénération', 'description' => 'Guérison rapide des blessures'],
            ['name' => 'Manipulation du Temps', 'description' => 'Ralentir ou accélérer le temps'],
            ['name' => 'Contrôle des Éléments', 'description' => 'Maîtrise du feu, de l\'eau, de l\'air et de la terre'],
            ['name' => 'Téléportation', 'description' => 'Se déplacer instantanément d\'un endroit à un autre'],
            ['name' => 'Vision Laser', 'description' => 'Projeter des rayons d\'énergie par les yeux']
        ];

        foreach ($powers as $powerData) {
            $power = new Power();
            $power->setName($powerData['name']);
            $power->setDescription($powerData['description']);
            $this->entityManager->persist($power);
        }

        $this->entityManager->flush();
        $io->success('Pouvoirs créés avec succès !');

        // Récupération de tous les pouvoirs pour les assigner aux héros
        $allPowers = $this->entityManager->getRepository(Power::class)->findAll();

        // Création des super-héros
        $heroes = [
            [
                'name' => 'Captain Nova',
                'alias' => 'Sarah Parker',
                'weakness' => 'Perd ses pouvoirs en présence de kryptonite rouge'
            ],
            [
                'name' => 'Shadow Walker',
                'alias' => 'John Shade',
                'weakness' => 'Ne peut pas utiliser ses pouvoirs en pleine lumière'
            ],
            [
                'name' => 'Thunder Storm',
                'alias' => 'Marcus Thunder',
                'weakness' => 'Vulnérable aux attaques électriques'
            ],
            [
                'name' => 'Mind Master',
                'alias' => 'Emma Grey',
                'weakness' => 'Maux de tête intenses après utilisation de ses pouvoirs'
            ],
            [
                'name' => 'Quantum',
                'alias' => 'Dr. Alex Quantum',
                'weakness' => 'Instabilité moléculaire après téléportation'
            ],
            [
                'name' => 'Phoenix Flame',
                'alias' => 'Maya Phoenix',
                'weakness' => 'Perd le contrôle de ses pouvoirs sous stress intense'
            ],
            [
                'name' => 'Ice Queen',
                'alias' => 'Elena Frost',
                'weakness' => 'Pouvoirs affaiblis dans les environnements chauds'
            ],
            [
                'name' => 'Steel Guardian',
                'alias' => 'James Steel',
                'weakness' => 'Mobilité réduite due à son armure'
            ],
            [
                'name' => 'Nature\'s Voice',
                'alias' => 'Luna Green',
                'weakness' => 'Pouvoirs limités en environnement urbain'
            ],
            [
                'name' => 'Time Keeper',
                'alias' => 'Dr. Christopher Clock',
                'weakness' => 'Vieillissement accéléré après manipulation du temps'
            ]
        ];

        foreach ($heroes as $heroData) {
            $hero = new SuperHero();
            $hero->setName($heroData['name']);
            $hero->setAlias($heroData['alias']);
            $hero->setWeakness($heroData['weakness']);
            $hero->setEnergy(100);
            
            // Assigner un pouvoir aléatoire
            $randomPower = $allPowers[array_rand($allPowers)];
            $hero->setPower($randomPower);
            
            $this->entityManager->persist($hero);
        }

        $this->entityManager->flush();
        $io->success('Super-héros créés avec succès !');

        return Command::SUCCESS;
    }
}
