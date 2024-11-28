<?php

namespace App\Service;

use App\Entity\Mission;

class RandomMissionGenerator
{
    private array $missionTemplates = [
        1 => [ // Facile
            [
                'title' => 'Sauver un chat coincé dans un arbre',
                'description' => 'Un chat est coincé dans un grand arbre du parc central. Une mission parfaite pour un débutant !',
                'timeLimit' => 300
            ],
            [
                'title' => 'Aider une grand-mère à traverser',
                'description' => 'Une grand-mère a besoin d\'aide pour traverser une rue très fréquentée.',
                'timeLimit' => 180
            ],
            [
                'title' => 'Retrouver un ballon perdu',
                'description' => 'Un enfant a perdu son ballon. Il faut le retrouver avant qu\'il ne s\'envole trop loin !',
                'timeLimit' => 240
            ]
        ],
        2 => [ // Moyenne
            [
                'title' => 'Arrêter un voleur à l\'étalage',
                'description' => 'Un voleur sévit dans le centre commercial. Il faut l\'arrêter avant qu\'il ne s\'échappe !',
                'timeLimit' => 600
            ],
            [
                'title' => 'Éteindre un incendie',
                'description' => 'Un petit incendie s\'est déclaré dans un immeuble. Les pompiers sont en route, mais il faut agir vite !',
                'timeLimit' => 480
            ],
            [
                'title' => 'Sécuriser un chantier dangereux',
                'description' => 'Un chantier mal sécurisé menace les passants. Il faut intervenir rapidement !',
                'timeLimit' => 420
            ]
        ],
        3 => [ // Difficile
            [
                'title' => 'Neutraliser un super-vilain',
                'description' => 'Un super-vilain terrorise le centre-ville. Cette mission requiert de l\'expérience et du courage !',
                'timeLimit' => 900
            ],
            [
                'title' => 'Désamorcer une bombe',
                'description' => 'Une bombe a été découverte dans un lieu public. Le temps presse !',
                'timeLimit' => 720
            ],
            [
                'title' => 'Empêcher un braquage de banque',
                'description' => 'Des malfaiteurs lourdement armés tentent de braquer la banque centrale !',
                'timeLimit' => 840
            ]
        ]
    ];

    public function generateMission(string $difficulty): Mission
    {
        // Convertir la difficulté en entier
        $difficultyMap = [
            'easy' => 1,
            'medium' => 2,
            'hard' => 3
        ];

        $difficultyLevel = $difficultyMap[$difficulty] ?? null;

        if (!isset($this->missionTemplates[$difficultyLevel])) {
            throw new \InvalidArgumentException('Difficulté invalide');
        }

        $templates = $this->missionTemplates[$difficultyLevel];
        $template = $templates[array_rand($templates)];

        $mission = new Mission();
        $mission->setTitle($template['title']);
        $mission->setDescription($template['description']);
        $mission->setTimeLimit($template['timeLimit']);
        $mission->setDifficulty($difficultyLevel);
        $mission->setStatus('pending');
        return $mission;
    }
}
