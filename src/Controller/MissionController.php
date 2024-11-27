<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\MissionHistory;
use App\Entity\SuperHero;
use App\Entity\Power;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use App\Repository\MissionHistoryRepository;
use App\Repository\PowerRepository;
use App\Repository\SuperHeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mission')]
class MissionController extends AbstractController
{
    #[Route('/', name: 'app_mission_index', methods: ['GET'])]
    public function index(MissionRepository $missionRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérifier et supprimer les missions expirées
        $missions = $missionRepository->findAll();
        
        return $this->render('mission/index.html.twig', [
            'missions' => $missions
        ]);
    }

    #[Route('/history', name: 'app_mission_history', methods: ['GET'])]
    public function history(MissionHistoryRepository $missionHistoryRepository): Response
    {
        return $this->render('mission/history.html.twig', [
            'mission_history' => $missionHistoryRepository->findBy([], ['deletedAt' => 'DESC']),
        ]);
    }

    #[Route('/history/clear', name: 'app_mission_history_clear', methods: ['POST'])]
    public function clearHistory(MissionHistoryRepository $missionHistoryRepository, EntityManagerInterface $entityManager): Response
    {
        $histories = $missionHistoryRepository->findAll();
        
        foreach ($histories as $history) {
            $entityManager->remove($history);
        }
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Historique effacé avec succès');
        return $this->redirectToRoute('app_mission_history');
    }

    #[Route('/new', name: 'app_mission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mission->setStatus('pending');
            $entityManager->persist($mission);
            $entityManager->flush();

            return $this->redirectToRoute('app_mission_index');
        }

        return $this->render('mission/new.html.twig', [
            'mission' => $mission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mission_show', methods: ['GET'])]
    public function show(Mission $mission, SuperHeroRepository $superHeroRepository): Response
    {
        return $this->render('mission/show.html.twig', [
            'mission' => $mission,
            'available_heroes' => $superHeroRepository->findAll(),
        ]);
    }

    #[Route('/{id}/assign', name: 'app_mission_assign', methods: ['POST'])]
    public function assign(
        Mission $mission,
        Request $request,
        SuperHeroRepository $superHeroRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($mission->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette mission n\'est pas disponible');
            return $this->redirectToRoute('app_mission_index');
        }

        $heroId = $request->request->get('heroId');
        if (!$heroId) {
            $this->addFlash('error', 'Aucun héros sélectionné');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        $hero = $superHeroRepository->find($heroId);
        if (!$hero) {
            $this->addFlash('error', 'Héros non trouvé');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        $mission->setSuperHero($hero);
        $mission->setStatus('in_progress');
        $mission->setStartedAt(new \DateTimeImmutable());
        
        $entityManager->flush();

        return $this->redirectToRoute('app_mission_progress', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/start', name: 'app_mission_start', methods: ['POST'])]
    public function start(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('start'.$mission->getId(), $request->request->get('_token'))) {
            $mission->setStatus('in_progress');
            $mission->setStartedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mission_progress', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/progress', name: 'app_mission_progress', methods: ['GET'])]
    public function progress(Mission $mission): Response
    {
        if ($mission->getStatus() !== 'in_progress') {
            return $this->redirectToRoute('app_mission_index');
        }

        return $this->render('mission/progress.html.twig', [
            'mission' => $mission,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_mission_cancel', methods: ['POST'])]
    public function cancel(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$mission->getId(), $request->request->get('_token'))) {
            $mission->setStatus('cancelled');
            
            // Ajouter des messages au journal
            $currentResult = $mission->getResult() ?? '';
            $log = [];
            $log[] = "[Erreur] Mission annulée !";
            
            // Ajouter un message sur l'état du héros
            $hero = $mission->getSuperHero();
            if ($hero) {
                $energyLoss = rand(5, 15);
                $newEnergy = max(0, $hero->getEnergy() - $energyLoss);
                $hero->setEnergy($newEnergy);
                
                if ($newEnergy < 20) {
                    $log[] = "[Attention] Le héros est fatigué ! Énergie restante : {$newEnergy}%";
                } else {
                    $log[] = "[Info] Énergie restante du héros : {$newEnergy}%";
                }
            }
            
            $mission->setResult($currentResult . "\n" . implode("\n", $log));
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/complete', name: 'app_mission_complete', methods: ['POST'])]
    public function complete(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('complete'.$mission->getId(), $request->request->get('_token'))) {
            $mission->setStatus('completed');
            
            // Calculer le score en fonction du temps restant
            $startTime = $mission->getStartedAt();
            $timeLimit = $mission->getTimeLimit();
            $elapsedTime = time() - $startTime->getTimestamp();
            $timeRatio = max(0, min(1, ($timeLimit - $elapsedTime) / $timeLimit));
            $score = round($timeRatio * 100);
            
            $mission->setScore($score);
            
            // Ajouter des messages au journal
            $currentResult = $mission->getResult() ?? '';
            $log = [];
            $log[] = "[Info] Mission terminée avec succès !";
            
            if ($score >= 90) {
                $log[] = "[Succès] Performance exceptionnelle ! Score: {$score}/100";
            } elseif ($score >= 70) {
                $log[] = "[Succès] Très bonne performance ! Score: {$score}/100";
            } elseif ($score >= 50) {
                $log[] = "[Attention] Performance moyenne. Score: {$score}/100";
            } else {
                $log[] = "[Attention] Performance à améliorer. Score: {$score}/100";
            }
            
            // Ajouter un message sur l'état du héros
            $hero = $mission->getSuperHero();
            if ($hero) {
                $energyLoss = rand(10, 30);
                $newEnergy = max(0, $hero->getEnergy() - $energyLoss);
                $hero->setEnergy($newEnergy);
                
                if ($newEnergy < 20) {
                    $log[] = "[Attention] Le héros est épuisé ! Énergie restante : {$newEnergy}%";
                } else {
                    $log[] = "[Info] Énergie restante du héros : {$newEnergy}%";
                }
            }
            
            $mission->setResult($currentResult . "\n" . implode("\n", $log));
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/update-progress', name: 'app_mission_update_progress', methods: ['POST'])]
    public function updateProgress(Mission $mission, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        return new JsonResponse([
            'success' => true,
            'progress' => $data['progress'] ?? 0,
        ]);
    }

    #[Route('/random', name: 'app_mission_random', methods: ['POST'])]
    public function createRandom(
        EntityManagerInterface $entityManager,
        Request $request,
        PowerRepository $powerRepository
    ): Response {
        if (!$this->isCsrfTokenValid('random_mission', $request->request->get('_token'))) {
            return $this->redirectToRoute('app_mission_index');
        }

        $titles = [
            "Sauvetage en haute mer",
            "Menace extraterrestre",
            "Braquage de banque",
            "Catastrophe naturelle",
            "Attaque de super-vilain",
            "Protection de dignitaire",
            "Course contre la montre",
            "Invasion de robots",
            "Sauvetage d'otages",
            "Déminage explosif"
        ];

        $descriptions = [
            "Un navire en détresse nécessite une intervention rapide !",
            "Des forces extraterrestres menacent la ville !",
            "Des criminels high-tech attaquent la banque centrale !",
            "Un tsunami approche de la côte, il faut évacuer la population !",
            "Un super-vilain sème la terreur dans le centre-ville !",
            "Un VIP important a besoin d'une protection rapprochée !",
            "Le temps presse, chaque seconde compte !",
            "Une armée de robots envahit la ville !",
            "Des civils sont retenus en otage !",
            "Une bombe menace d'exploser !"
        ];

        $mission = new Mission();
        $mission->setTitle($titles[array_rand($titles)]);
        $mission->setDescription($descriptions[array_rand($descriptions)]);
        $mission->setDifficulty(rand(1, 5));
        $mission->setStatus('pending');
        $mission->setStartedAt(new \DateTimeImmutable());

        // Calculer le temps limite en fonction de la difficulté (2-5 minutes)
        $timeLimit = min(2 + ($mission->getDifficulty() - 1), 5) * 60; // Conversion en secondes
        $mission->setTimeLimit($timeLimit);

        // Sélectionner aléatoirement 1 à 3 pouvoirs requis
        $allPowers = $powerRepository->findAll();
        $numPowers = rand(1, 3);
        $selectedPowers = array_slice($allPowers, 0, $numPowers);
        foreach ($selectedPowers as $power) {
            $mission->addRequiredPower($power);
        }

        $entityManager->persist($mission);
        $entityManager->flush();

        $this->addFlash('success', 'Une nouvelle mission aléatoire a été créée !');
        return $this->redirectToRoute('app_mission_index');
    }

    #[Route('/generate-random', name: 'app_mission_generate_random', methods: ['POST'])]
    public function generateRandom(EntityManagerInterface $entityManager): Response
    {
        $mission = new Mission();
        
        // Titres possibles
        $titles = [
            'Sauver le monde',
            'Arrêter un super-vilain',
            'Protéger la ville',
            'Désamorcer une bombe',
            'Empêcher un braquage',
            'Secourir des otages',
            'Neutraliser une menace',
            'Escorter un VIP',
            'Infiltrer une base secrète',
            'Récupérer un artefact'
        ];
        
        // Descriptions possibles
        $descriptions = [
            'Une mission cruciale pour la survie de l\'humanité.',
            'Un super-vilain menace la paix mondiale.',
            'La ville est en danger, nous avons besoin de vous !',
            'Une bombe a été placée dans un lieu stratégique.',
            'Des criminels tentent de braquer la banque centrale.',
            'Des civils sont retenus en otage.',
            'Une menace inconnue a été détectée.',
            'Un VIP important doit être protégé.',
            'Une base secrète cache des informations cruciales.',
            'Un artefact ancien doit être récupéré avant qu\'il ne tombe entre de mauvaises mains.'
        ];

        // Sélection aléatoire
        $mission->setTitle($titles[array_rand($titles)]);
        $mission->setDescription($descriptions[array_rand($descriptions)]);
        $mission->setDifficulty(rand(1, 5));

        // Ajout de pouvoirs requis aléatoires
        $powers = $entityManager->getRepository(Power::class)->findAll();
        if (!empty($powers)) {
            $numPowers = rand(1, min(3, count($powers)));
            shuffle($powers);
            for ($i = 0; $i < $numPowers; $i++) {
                $mission->addRequiredPower($powers[$i]);
            }
        }

        $entityManager->persist($mission);
        $entityManager->flush();

        $this->addFlash('success', 'Une nouvelle mission a été générée avec succès !');
        return $this->redirectToRoute('app_mission_index');
    }

    #[Route('/cleanup-cancelled', name: 'app_mission_cleanup', methods: ['GET'])]
    public function cleanupCancelledMissions(EntityManagerInterface $entityManager, MissionRepository $missionRepository): Response
    {
        $now = new \DateTimeImmutable();
        $deletedCount = 0;

        // Récupérer toutes les missions annulées
        $cancelledMissions = $missionRepository->findBy(['status' => 'cancelled']);

        foreach ($cancelledMissions as $mission) {
            if ($mission->getCancelledAt()) {
                $deleteDelay = min(2 + ($mission->getDifficulty() - 1), 5) * 60; // En secondes
                $deleteTime = $mission->getCancelledAt()->modify("+{$deleteDelay} seconds");

                if ($now >= $deleteTime) {
                    $entityManager->remove($mission);
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $entityManager->flush();
            $this->addFlash('success', sprintf('%d mission(s) supprimée(s) avec succès.', $deletedCount));
        } else {
            $this->addFlash('info', 'Aucune mission à supprimer pour le moment.');
        }

        return $this->redirectToRoute('app_mission_index');
    }

    #[Route('/{id}/delete', name: 'app_mission_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Mission $mission, 
        EntityManagerInterface $entityManager,
        MissionRepository $missionRepository,
        MissionHistoryRepository $missionHistoryRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            // Vérifier que la mission peut être supprimée
            if ($mission->getStatus() === 'completed' || $mission->getStatus() === 'cancelled') {
                $this->archiveAndDeleteMission($mission, $missionRepository, $missionHistoryRepository, $entityManager);
                $this->addFlash('success', 'La mission a été archivée et supprimée avec succès.');
            } else {
                $this->addFlash('error', 'Seules les missions terminées ou annulées peuvent être supprimées.');
            }
        }

        return $this->redirectToRoute('app_mission_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/assign-hero', name: 'app_mission_assign_hero', methods: ['GET'])]
    public function assignHero(Mission $mission, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les héros qui n'ont pas de mission active
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sh')
           ->from(SuperHero::class, 'sh')
           ->leftJoin('sh.missions', 'm')
           ->where('m.id IS NULL OR m.status = :completed OR m.status = :cancelled')
           ->setParameter('completed', 'completed')
           ->setParameter('cancelled', 'cancelled');
        
        $availableHeroes = $qb->getQuery()->getResult();
        
        return $this->render('mission/assign_hero.html.twig', [
            'mission' => $mission,
            'heroes' => $availableHeroes
        ]);
    }

    #[Route('/{id}/assign-hero/{heroId}', name: 'app_mission_assign_hero_post', methods: ['POST'])]
    public function assignHeroPost(Mission $mission, int $heroId, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('assign' . $mission->getId() . $heroId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $hero = $entityManager->getRepository(SuperHero::class)->find($heroId);
        if (!$hero) {
            throw $this->createNotFoundException('Héros non trouvé');
        }

        $mission->setSuperHero($hero);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Le héros %s a été assigné à la mission !', $hero->getName()));
        return $this->redirectToRoute('app_mission_index');
    }

    #[Route('/{id}', name: 'app_mission_delete', methods: ['POST'])]
    public function deleteMission(
        Request $request, 
        Mission $mission, 
        EntityManagerInterface $entityManager,
        MissionRepository $missionRepository,
        MissionHistoryRepository $missionHistoryRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            // Vérifier que la mission peut être supprimée
            if ($mission->getStatus() === 'completed' || $mission->getStatus() === 'cancelled') {
                $this->archiveAndDeleteMission($mission, $missionRepository, $missionHistoryRepository, $entityManager);
                $this->addFlash('success', 'La mission a été archivée et supprimée avec succès.');
            } else {
                $this->addFlash('error', 'Seules les missions terminées ou annulées peuvent être supprimées.');
            }
        }

        return $this->redirectToRoute('app_mission_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_mission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Mission updated successfully.');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        return $this->render('mission/edit.html.twig', [
            'mission' => $mission,
            'form' => $form,
        ]);
    }

    private function archiveAndDeleteMission(Mission $mission, MissionRepository $missionRepository, MissionHistoryRepository $missionHistoryRepository, EntityManagerInterface $entityManager): void
    {
        // Créer une entrée dans l'historique
        $missionHistory = new MissionHistory();
        $missionHistory->setTitle($mission->getTitle())
            ->setDescription($mission->getDescription())
            ->setDifficulty($mission->getDifficulty())
            ->setStatus($mission->getStatus())
            ->setDeletedAt(new \DateTimeImmutable())
            ->setScore($mission->getScore())
            ->setResult($mission->getResult());

        if ($mission->getSuperHero()) {
            $missionHistory->setHeroName($mission->getSuperHero()->getName());
        }

        $entityManager->persist($missionHistory);
        $entityManager->remove($mission);
        $entityManager->flush();
    }

    private function calculateScore(Mission $mission, int $efficiency, int $energy): int
    {
        // Score de base basé sur la difficulté (1-5) * 1000
        $baseScore = $mission->getDifficulty() * 1000;
        
        // Bonus d'efficacité (0-100%) * difficulté * 100
        $efficiencyBonus = ($efficiency / 100) * $mission->getDifficulty() * 100;
        
        // Bonus d'énergie restante (0-100%) * difficulté * 50
        $energyBonus = ($energy / 100) * $mission->getDifficulty() * 50;
        
        // Score final
        return (int) ($baseScore + $efficiencyBonus + $energyBonus);
    }
}
