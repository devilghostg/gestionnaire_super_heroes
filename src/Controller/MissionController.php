<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\MissionHistory;
use App\Entity\SuperHero;
use App\Entity\Power;
use App\Entity\Team;
use App\Entity\MissionAssignment;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use App\Repository\MissionHistoryRepository;
use App\Repository\PowerRepository;
use App\Repository\SuperHeroRepository;
use App\Repository\TeamRepository;
use App\Repository\MissionAssignmentRepository;
use App\Service\RandomMissionGenerator;
use App\Service\MissionLogGenerator;
use App\Service\EnergyRechargeService;
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

    #[Route('/create-random', name: 'app_mission_random', methods: ['GET', 'POST'])]
    public function random(Request $request, EntityManagerInterface $entityManager, RandomMissionGenerator $generator): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('random_mission', $request->request->get('_token'))) {
                $this->addFlash('error', 'Token CSRF invalide');
                return $this->redirectToRoute('app_mission_index');
            }

            $difficulty = $request->request->get('difficulty');
            
            try {
                $mission = $generator->generateMission($difficulty);
                
                // Définir la date de début
                $mission->setStartedAt(new \DateTimeImmutable());
                
                $entityManager->persist($mission);
                $entityManager->flush();

                $this->addFlash('success', 'Mission aléatoire créée avec succès !');
                return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de la mission : ' . $e->getMessage());
            }
        }

        return $this->render('mission/random.html.twig');
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
            if ($mission->getStatus() !== 'pending') {
                $this->addFlash('error', 'Cette mission ne peut pas être démarrée');
                return $this->redirectToRoute('app_mission_index');
            }

            // Vérifier si un héros est assigné
            if (!$mission->getSuperHero()) {
                $this->addFlash('error', 'Aucun héros n\'est assigné à cette mission');
                return $this->redirectToRoute('app_mission_index');
            }

            // Vérifier l'énergie du héros
            $hero = $mission->getSuperHero();
            if ($hero->getEnergy() < 20) {
                $this->addFlash('error', 'Le héros n\'a pas assez d\'énergie pour cette mission');
                return $this->redirectToRoute('app_mission_index');
            }

            // Initialiser l'énergie si nécessaire
            if ($hero->getEnergy() === null) {
                $hero->setEnergy(100);
            }

            $mission->setStatus('in_progress');
            $mission->setStartedAt(new \DateTimeImmutable());
            
            // Ajouter un message de début de mission
            $currentResult = $mission->getResult() ?? '';
            $log = [];
            $log[] = "[Info] Mission démarrée !";
            $log[] = "[Info] Héros assigné : " . $hero->getName();
            $log[] = "[Info] Énergie initiale : " . $hero->getEnergy() . "%";
            
            $mission->setResult($currentResult . "\n" . implode("\n", $log));
            
            $entityManager->persist($mission);
            $entityManager->flush();

            return $this->redirectToRoute('app_mission_progress', ['id' => $mission->getId()]);
        }

        return $this->redirectToRoute('app_mission_index');
    }

    #[Route('/{id}/progress', name: 'app_mission_progress', methods: ['GET'])]
    public function progress(
        Mission $mission,
        EnergyRechargeService $energyRechargeService
    ): Response {
        if ($mission->getStatus() !== 'in_progress') {
            return $this->redirectToRoute('app_mission_index');
        }

        // Vérifier si le héros a assez d'énergie
        $hero = $mission->getSuperHero();
        if ($hero) {
            // Recharger l'énergie avant de vérifier
            $energyRechargeService->rechargeEnergy($hero);
            
            if ($hero->getEnergy() < 10) {
                $this->addFlash('warning', 'Le héros est trop fatigué pour continuer la mission !');
                return $this->redirectToRoute('app_mission_index');
            }
        }

        return $this->render('mission/progress.html.twig', [
            'mission' => $mission
        ]);
    }

    #[Route('/{id}/progress/update', name: 'app_mission_update_progress', methods: ['POST'])]
    public function updateProgress(
        Mission $mission,
        Request $request,
        EntityManagerInterface $entityManager,
        MissionLogGenerator $logGenerator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $progress = $data['progress'] ?? 0;

        // Mettre à jour la progression
        $mission->setProgress($progress);

        // Mettre à jour l'énergie du héros
        $hero = $mission->getSuperHero();
        if ($hero) {
            $energyLoss = 0.5; // Perte d'énergie progressive
            $newEnergy = max(0, $hero->getEnergy() - $energyLoss);
            $hero->setEnergy($newEnergy);
            $entityManager->persist($hero);
        }

        // Générer des logs de mission
        $logs = $logGenerator->generateLog($progress);
        
        // Ajouter les logs au résultat de la mission
        $currentResult = $mission->getResult() ?? '';
        foreach ($logs as $log) {
            $currentResult .= "[" . (new \DateTime())->format('H:i:s') . "] " . $log . "\n";
        }
        $mission->setResult($currentResult);

        $entityManager->persist($mission);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'progress' => $progress,
            'heroEnergy' => $hero ? $hero->getEnergy() : null,
            'logs' => $logs
        ]);
    }

    #[Route('/{id}/complete', name: 'app_mission_complete', methods: ['POST'])]
    public function complete(
        Request $request, 
        Mission $mission, 
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if ($this->isCsrfTokenValid('complete'.$mission->getId(), $request->request->get('_token'))) {
            $mission->setStatus('completed');
            $mission->setCompletedAt(new \DateTime());
            
            // Calculer le score final
            $startTime = $mission->getStartedAt();
            $timeLimit = $mission->getTimeLimit();
            $elapsedTime = time() - $startTime->getTimestamp();
            $timeRatio = max(0, min(1, ($timeLimit - $elapsedTime) / $timeLimit));
            $score = round($timeRatio * 100);
            
            $mission->setScore($score);
            
            // Mettre à jour la dernière mission du héros
            $hero = $mission->getSuperHero();
            if ($hero) {
                $hero->setLastMission($mission);
                $entityManager->persist($hero);
            }
            
            $entityManager->persist($mission);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Mission terminée avec succès !',
                'score' => $score,
                'redirect' => $this->generateUrl('app_mission_show', ['id' => $mission->getId()])
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], 400);
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

    #[Route('/{id}/delete', name: 'app_mission_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Mission $mission,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            try {
                // Trouver tous les héros qui ont cette mission comme dernière mission
                $heroes = $entityManager->getRepository(SuperHero::class)->findBy(['lastMission' => $mission]);
                
                // Désassocier la mission de tous ces héros
                foreach ($heroes as $hero) {
                    $hero->setLastMission(null);
                    $entityManager->persist($hero);
                }
                
                // Désassocier le héros de la mission
                if ($mission->getSuperHero()) {
                    $hero = $mission->getSuperHero();
                    $mission->setSuperHero(null);
                    $entityManager->persist($mission);
                }
                
                // Supprimer la mission
                $entityManager->remove($mission);
                $entityManager->flush();
                
                $this->addFlash('success', 'Mission supprimée avec succès');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression de la mission : ' . $e->getMessage());
            }
        }

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

    #[Route('/{id}/assign-heroes', name: 'app_mission_assign_heroes', methods: ['GET'])]
    public function assignHeroes(Mission $mission, SuperHeroRepository $heroRepository): Response
    {
        // Récupérer tous les héros disponibles (pas en mission)
        $availableHeroes = $heroRepository->findAvailableHeroes();
        
        return $this->render('mission/assign_heroes.html.twig', [
            'mission' => $mission,
            'available_heroes' => $availableHeroes
        ]);
    }

    #[Route('/{id}/assign-team', name: 'app_mission_assign_team', methods: ['GET'])]
    public function assignTeam(Mission $mission, TeamRepository $teamRepository): Response
    {
        // Récupérer les équipes disponibles (dont les héros ne sont pas en mission)
        $availableTeams = $teamRepository->findAvailableTeams();
        
        return $this->render('mission/assign_team.html.twig', [
            'mission' => $mission,
            'available_teams' => $availableTeams
        ]);
    }

    #[Route('/{id}/add-hero/{heroId}', name: 'app_mission_add_hero', methods: ['POST'])]
    public function addHeroToMission(
        Mission $mission,
        SuperHero $hero,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifier si le héros est disponible
        if (!$hero->isAvailableForMission()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ce héros est déjà en mission'
            ], 400);
        }

        // Créer une nouvelle assignation
        $assignment = new MissionAssignment();
        $assignment->setMission($mission)
                  ->setHero($hero)
                  ->setIsActive(true);

        $entityManager->persist($assignment);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Héros ajouté à la mission avec succès'
        ]);
    }

    #[Route('/{id}/add-team/{teamId}', name: 'app_mission_add_team', methods: ['POST'])]
    public function addTeamToMission(
        Mission $mission,
        Team $team,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifier si tous les héros de l'équipe sont disponibles
        foreach ($team->getMembers() as $hero) {
            if (!$hero->isAvailableForMission()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Certains héros de l\'équipe sont déjà en mission'
                ], 400);
            }
        }

        // Assigner tous les héros de l'équipe
        foreach ($team->getMembers() as $hero) {
            $assignment = new MissionAssignment();
            $assignment->setMission($mission)
                      ->setHero($hero)
                      ->setIsActive(true);
            $entityManager->persist($assignment);
        }

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Équipe assignée à la mission avec succès'
        ]);
    }

    #[Route('/{id}/remove-hero/{heroId}', name: 'app_mission_remove_hero', methods: ['POST'])]
    public function removeHeroFromMission(
        Mission $mission,
        SuperHero $hero,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $assignment = $entityManager->getRepository(MissionAssignment::class)
            ->findOneBy(['mission' => $mission, 'hero' => $hero, 'isActive' => true]);

        if (!$assignment) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ce héros n\'est pas assigné à cette mission'
            ], 404);
        }

        $assignment->setIsActive(false);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Héros retiré de la mission avec succès'
        ]);
    }

    #[Route('/{id}/delete', name: 'app_mission_delete', methods: ['POST'])]
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

    private function archiveAndDeleteMission(
        Mission $mission,
        MissionRepository $missionRepository,
        MissionHistoryRepository $missionHistoryRepository,
        EntityManagerInterface $entityManager
    ): void {
        // Créer l'entrée d'historique
        $missionHistory = new MissionHistory();
        $missionHistory->setTitle($mission->getTitle());
        $missionHistory->setDescription($mission->getDescription());
        $missionHistory->setDifficulty($mission->getDifficulty());
        $missionHistory->setStatus($mission->getStatus());
        $missionHistory->setDeletedAt(new \DateTimeImmutable());
        
        if ($mission->getSuperHero()) {
            $missionHistory->setHeroName($mission->getSuperHero()->getName());
        }
        
        // Sauvegarder l'historique
        $entityManager->persist($missionHistory);
        
        // Nettoyer les relations
        $heroes = $entityManager->getRepository(SuperHero::class)->findBy(['lastMission' => $mission]);
        foreach ($heroes as $hero) {
            $hero->setLastMission(null);
            $entityManager->persist($hero);
        }
        
        // Désassocier le héros de la mission
        if ($mission->getSuperHero()) {
            $hero = $mission->getSuperHero();
            $mission->setSuperHero(null);
            $entityManager->persist($mission);
        }
        
        // Supprimer la mission
        $entityManager->remove($mission);
        
        // Sauvegarder tous les changements
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
