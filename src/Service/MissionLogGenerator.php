<?php

namespace App\Service;

class MissionLogGenerator
{
    private array $actionMessages = [
        'combat' => [
            "Le héros affronte un groupe d'ennemis !",
            "Une bataille intense fait rage !",
            "Des coups sont échangés avec férocité !",
            "Le héros esquive une attaque surprise !",
            "Une manœuvre défensive est exécutée !"
        ],
        'rescue' => [
            "Des civils sont mis en sécurité !",
            "Une opération de sauvetage est en cours !",
            "Le héros aide des personnes piégées !",
            "L'évacuation progresse bien !",
            "Des victimes sont secourues !"
        ],
        'investigation' => [
            "Des indices importants sont découverts !",
            "Le héros suit une piste prometteuse !",
            "Une zone suspecte est inspectée !",
            "Des informations cruciales sont récoltées !",
            "Une découverte intéressante est faite !"
        ],
        'energy' => [
            "Le héros puise dans ses réserves d'énergie !",
            "L'effort commence à se faire sentir...",
            "La fatigue s'accumule progressivement.",
            "Le héros maintient son rythme malgré la fatigue.",
            "L'énergie diminue mais la détermination reste !"
        ]
    ];

    public function generateLog(int $progress): array
    {
        $messages = [];
        
        // Sélectionner une catégorie aléatoire
        $categories = array_keys($this->actionMessages);
        $category = $categories[array_rand($categories)];
        
        // Ajouter un message principal
        $messages[] = $this->actionMessages[$category][array_rand($this->actionMessages[$category])];
        
        // Ajouter un message sur l'énergie si le progress est un multiple de 20
        if ($progress % 20 === 0) {
            $messages[] = $this->actionMessages['energy'][array_rand($this->actionMessages['energy'])];
        }
        
        return $messages;
    }
}
