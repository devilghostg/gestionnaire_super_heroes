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
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Créer une requête DQL pour charger les missions avec leurs héros et pouvoirs
        $dql = "
            SELECT m, a, h, p
            FROM App\Entity\Mission m
            LEFT JOIN m.assignments a
            LEFT JOIN a.hero h
            LEFT JOIN h.powers p
            WHERE a.isActive = true OR a.isActive IS NULL
            ORDER BY m.id DESC
        ";
        
        $query = $entityManager->createQuery($dql);
        $missions = $query->getResult();
        
        // Mettre à jour la progression des missions en cours
        foreach ($missions as $mission) {
            if ($mission->getStatus() === 'in_progress') {
                $elapsedTime = time() - $mission->getStartedAt()->getTimestamp();
                $totalTime = $mission->getTimeLimit() * 60;
                $progress = min(100, (int)(($elapsedTime / $totalTime) * 100));
                $mission->setProgress($progress);
                
                if ($progress >= 100) {
                    $mission->setStatus('completed');
                    $mission->setCompletedAt(new \DateTime());
                    $entityManager->flush();
                }
            }
        }
        
        return $this->render('mission/index.html.twig', [
            'missions' => $missions,
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

    #[Route('/{id}/start', name: 'app_mission_start', methods: ['POST'])]
    public function startMission(Mission $mission, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si la mission peut être démarrée
        if ($mission->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette mission ne peut pas être démarrée car elle n\'est pas en attente.');
            return $this->redirectToRoute('app_mission_index');
        }

        // Vérifier les héros assignés
        $activeAssignments = $mission->getActiveAssignments();
        if ($activeAssignments->isEmpty()) {
            $this->addFlash('error', 'Il n\'y a pas de héros assignés à cette mission.');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        // Vérifier l'énergie des héros
        foreach ($activeAssignments as $assignment) {
            $hero = $assignment->getHero();
            if ($hero->getEnergy() < 20) {
                $this->addFlash('error', sprintf('Le héros %s n\'a pas assez d\'énergie pour cette mission.', $hero->getName()));
                return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
            }
        }

        // Démarrer la mission
        $mission->setStatus('in_progress');
        $mission->setStartedAt(new \DateTimeImmutable());
        $mission->setProgress(0);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'La mission a été démarrée avec succès !');
        
        // Rediriger vers la page de progression
        return $this->redirectToRoute('app_mission_progress', [
            'id' => $mission->getId()
        ]);
    }

    #[Route('/{id}/progress', name: 'app_mission_progress', methods: ['GET'])]
    public function progress(
        Mission $mission,
        EnergyRechargeService $energyRechargeService
    ): Response {
        if ($mission->getStatus() !== 'in_progress') {
            return $this->redirectToRoute('app_mission_index');
        }

        // Vérifier si les héros ont assez d'énergie
        foreach ($mission->getActiveAssignments() as $assignment) {
            $hero = $assignment->getHero();
            // Recharger l'énergie avant de vérifier
            $energyRechargeService->rechargeEnergy($hero);
            
            if ($hero->getEnergy() < 10) {
                $this->addFlash('warning', sprintf('Le héros %s est trop fatigué pour continuer la mission !', $hero->getName()));
            }
        }

        if ($mission->getActiveAssignments()->isEmpty()) {
            $this->addFlash('error', 'Il n\'y a plus de héros disponibles pour cette mission !');
            return $this->redirectToRoute('app_mission_index');
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

        // Mettre à jour l'énergie des héros
        foreach ($mission->getActiveAssignments() as $assignment) {
            $energyLoss = 0.5; // Perte d'énergie progressive
            $newEnergy = max(0, $assignment->getEnergy() - $energyLoss);
            $assignment->setEnergy($newEnergy);
            $entityManager->persist($assignment);
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

        $heroEnergies = [];
        foreach ($mission->getActiveAssignments() as $assignment) {
            $heroEnergies[$assignment->getHero()->getId()] = $assignment->getEnergy();
        }

        return new JsonResponse([
            'success' => true,
            'progress' => $progress,
            'heroEnergies' => $heroEnergies,
            'logs' => $logs
        ]);
    }

    #[Route('/{id}/complete', name: 'app_mission_complete', methods: ['POST'])]
    public function completeMission(
        Mission $mission,
        Request $request,
        EntityManagerInterface $entityManager,
        MissionHistoryRepository $historyRepository
    ): Response {
        if (!$this->isCsrfTokenValid('complete'.$mission->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        if ($mission->getStatus() !== 'in_progress') {
            $this->addFlash('error', 'Cette mission ne peut pas être terminée');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        // Créer un historique de la mission
        $history = new MissionHistory();
        $history->setTitle($mission->getTitle());
        $history->setDescription($mission->getDescription());
        $history->setDifficulty($mission->getDifficulty());
        $history->setResult($mission->getResult());
        $history->setStatus('completed');
        $history->setDeletedAt(new \DateTimeImmutable());
        
        // Ajouter les héros qui ont participé
        foreach ($mission->getActiveAssignments() as $assignment) {
            $history->addHero($assignment->getHero());
            
            // Recharger l'énergie du héros
            $hero = $assignment->getHero();
            $hero->setEnergy(100);
            $entityManager->persist($hero);
        }

        // Sauvegarder l'historique
        $entityManager->persist($history);

        // Supprimer la mission
        $entityManager->remove($mission);
        $entityManager->flush();

        $this->addFlash('success', 'Mission terminée avec succès ! Les héros peuvent maintenant se reposer.');
        return $this->redirectToRoute('app_mission_index');
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
            
            // Ajouter un message sur l'état des héros
            foreach ($mission->getAssignments() as $assignment) {
                $hero = $assignment->getHero();
                $energyLoss = rand(5, 15);
                $newEnergy = max(0, $hero->getEnergy() - $energyLoss);
                $hero->setEnergy($newEnergy);
                
                if ($newEnergy < 20) {
                    $log[] = sprintf("[Attention] Le héros %s est fatigué ! Énergie restante : %d%%", $hero->getName(), $newEnergy);
                } else {
                    $log[] = sprintf("[Info] Énergie restante du héros %s : %d%%", $hero->getName(), $newEnergy);
                }

                $assignment->setIsActive(false);
            }
            
            $mission->setResult($currentResult . "\n" . implode("\n", $log));
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/remove', name: 'app_mission_remove', methods: ['POST'])]
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

    #[Route('/{id}/assign-hero', name: 'app_mission_assign_hero', methods: ['GET', 'POST'])]
    public function assignHero(Request $request, Mission $mission, EntityManagerInterface $entityManager, SuperHeroRepository $superHeroRepository): Response
    {
        if ($request->isMethod('POST')) {
            $heroId = $request->request->get('hero_id');
            
            // Charger le héros avec ses pouvoirs
            $hero = $entityManager->createQuery(
                'SELECT h, p 
                FROM App\Entity\SuperHero h 
                LEFT JOIN h.powers p 
                WHERE h.id = :heroId'
            )
            ->setParameter('heroId', $heroId)
            ->getOneOrNullResult();
            
            if ($hero) {
                // Créer une nouvelle assignation
                $assignment = new MissionAssignment();
                $assignment->setMission($mission)
                          ->setHero($hero)
                          ->setIsActive(true)
                          ->setAssignedAt(new \DateTimeImmutable())
                          ->setEnergy($hero->getEnergy());

                $entityManager->persist($assignment);
                $mission->addAssignment($assignment);
                $entityManager->flush();
                
                $this->addFlash('success', 'Héros assigné avec succès à la mission.');
                return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
            }
        }
        
        // Récupérer tous les héros disponibles avec leurs pouvoirs
        $availableHeroes = $entityManager->createQuery(
            'SELECT h, p 
            FROM App\Entity\SuperHero h 
            LEFT JOIN h.powers p 
            WHERE h.id NOT IN (
                SELECT IDENTITY(ma.hero) 
                FROM App\Entity\MissionAssignment ma
                WHERE ma.isActive = true
            )'
        )->getResult();
        
        return $this->render('mission/assign_hero.html.twig', [
            'mission' => $mission,
            'available_heroes' => $availableHeroes,
        ]);
    }

    #[Route('/{id}/assign-hero/{heroId}', name: 'app_mission_assign_hero_post', methods: ['POST'])]
    public function assignHeroPost(
        Mission $mission,
        int $heroId,
        Request $request,
        SuperHeroRepository $superHeroRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('assign_hero'.$mission->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        if ($mission->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette mission n\'est pas disponible pour l\'assignation');
            return $this->redirectToRoute('app_mission_index');
        }

        $hero = $superHeroRepository->find($heroId);
        if (!$hero) {
            $this->addFlash('error', 'Héros non trouvé');
            return $this->redirectToRoute('app_mission_assign_hero', ['id' => $mission->getId()]);
        }

        // Vérifier si le héros n'est pas déjà assigné
        if ($mission->hasActiveHero($hero)) {
            $this->addFlash('error', 'Ce héros est déjà assigné à cette mission');
            return $this->redirectToRoute('app_mission_assign_hero', ['id' => $mission->getId()]);
        }

        // Vérifier l'énergie du héros
        if ($hero->getEnergy() < 20) {
            $this->addFlash('error', 'Le héros n\'a pas assez d\'énergie pour cette mission');
            return $this->redirectToRoute('app_mission_assign_hero', ['id' => $mission->getId()]);
        }

        // Créer une nouvelle assignation
        $assignment = new MissionAssignment();
        $assignment->setMission($mission)
                  ->setHero($hero)
                  ->setIsActive(true)
                  ->setAssignedAt(new \DateTimeImmutable())
                  ->setEnergy($hero->getEnergy());

        $entityManager->persist($assignment);
        $mission->addAssignment($assignment);
        $entityManager->flush();

        $this->addFlash('success', 'Héros assigné avec succès à la mission');
        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/remove-hero/{assignment}', name: 'app_mission_remove_hero', methods: ['POST'])]
    public function removeHero(
        Mission $mission,
        MissionAssignment $assignment,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($assignment->getMission() !== $mission) {
            $this->addFlash('error', 'Cet assignment n\'appartient pas à cette mission');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        if (!$this->isCsrfTokenValid('remove_hero'.$assignment->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        if ($mission->getStatus() !== 'pending') {
            $this->addFlash('error', 'Impossible de retirer un héros d\'une mission en cours ou terminée');
            return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
        }

        $entityManager->remove($assignment);
        $entityManager->flush();

        $this->addFlash('success', 'Le héros a été retiré de la mission avec succès');
        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
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
        
        // Sauvegarder les noms des héros assignés
        $heroNames = [];
        foreach ($mission->getAssignments() as $assignment) {
            if ($assignment->isActive()) {
                $heroNames[] = $assignment->getHero()->getName();
            }
        }
        $missionHistory->setHeroName(implode(', ', $heroNames));
        
        // Sauvegarder l'historique
        $entityManager->persist($missionHistory);
        
        // Nettoyer les relations
        foreach ($mission->getAssignments() as $assignment) {
            $entityManager->remove($assignment);
        }
        
        // Supprimer la mission
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
