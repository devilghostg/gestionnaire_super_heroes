<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\SuperHero;
use App\Form\MissionType;
use App\Repository\MissionRepository;
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
    public function index(MissionRepository $missionRepository): Response
    {
        return $this->render('mission/index.html.twig', [
            'missions' => $missionRepository->findAll(),
            'completed_missions' => $missionRepository->findCompletedMissionsOrderedByScore(),
        ]);
    }

    #[Route('/new', name: 'app_mission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mission = new Mission();
        $mission->setStatus('pending');
        $mission->setStartedAt(new \DateTimeImmutable());
        
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/{id}/assign/{heroId}', name: 'app_mission_assign', methods: ['POST'])]
    public function assign(
        Mission $mission,
        int $heroId,
        SuperHeroRepository $superHeroRepository,
        EntityManagerInterface $entityManager
    ): Response {
        if ($mission->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette mission n\'est pas disponible');
            return $this->redirectToRoute('app_mission_index');
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

    #[Route('/{id}/progress', name: 'app_mission_progress', methods: ['GET'])]
    public function progress(Mission $mission): Response
    {
        if ($mission->getStatus() !== 'in_progress') {
            $this->addFlash('error', 'Cette mission n\'est pas en cours');
            return $this->redirectToRoute('app_mission_index');
        }

        return $this->render('mission/progress.html.twig', [
            'mission' => $mission,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_mission_cancel', methods: ['POST'])]
    public function cancel(
        Mission $mission,
        EntityManagerInterface $entityManager
    ): Response {
        if ($mission->getStatus() !== 'in_progress') {
            $this->addFlash('error', 'Cette mission ne peut pas être annulée');
            return $this->redirectToRoute('app_mission_index');
        }

        $mission->setStatus('cancelled');
        $mission->setResult('Mission annulée par le superviseur');
        $mission->setCompletedAt(new \DateTimeImmutable());
        
        $entityManager->flush();

        $this->addFlash('warning', 'La mission a été annulée');
        return $this->redirectToRoute('app_mission_index');
    }

    #[Route('/{id}/complete', name: 'app_mission_complete', methods: ['POST'])]
    public function complete(
        Mission $mission,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($mission->getStatus() !== 'in_progress') {
            return new JsonResponse(['error' => 'Mission non disponible'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $efficiency = $data['efficiency'] ?? 0;
        $energy = $data['energy'] ?? 0;
        $events = $data['events'] ?? [];

        // Calcul du score basé sur l'efficacité et l'énergie restante
        $score = $this->calculateScore($mission, $efficiency, $energy);
        
        $mission->setStatus('completed');
        $mission->setCompletedAt(new \DateTimeImmutable());
        $mission->setScore($score);
        
        // Création d'un résumé de mission
        $successCount = count(array_filter($events, fn($event) => $event['success']));
        $totalEvents = count($events);
        $successRate = $totalEvents > 0 ? ($successCount / $totalEvents) * 100 : 0;
        
        $result = sprintf(
            "Mission accomplie avec %d%% de succès.\nEfficacité finale : %d%%\nÉnergie restante : %d%%\nScore final : %d points",
            round($successRate),
            $efficiency,
            $energy,
            $score
        );
        
        $mission->setResult($result);
        
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/delete', name: 'app_mission_delete', methods: ['POST'])]
    public function delete(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            $entityManager->remove($mission);
            $entityManager->flush();
            $this->addFlash('success', 'La mission a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_mission_index');
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
            "Un VIP important a besoin de protection rapprochée !",
            "Une bombe à retardement menace la ville !",
            "Une armée de robots envahit les rues !",
            "Des terroristes retiennent des civils en otage !",
            "Une bombe sophistiquée doit être désamorcée !"
        ];

        // Récupérer tous les pouvoirs disponibles
        $allPowers = $powerRepository->findAll();
        
        // Sélectionner un nombre aléatoire de pouvoirs (entre 1 et 3)
        $numPowers = rand(1, 3);
        shuffle($allPowers);
        $selectedPowers = array_slice($allPowers, 0, $numPowers);

        $mission = new Mission();
        $mission->setTitle($titles[array_rand($titles)]);
        $mission->setDescription($descriptions[array_rand($descriptions)]);
        $mission->setDifficulty(rand(1, 5));
        $mission->setTimeLimit(rand(1, 5) * 60); // Temps en multiples de 60 secondes
        $mission->setStatus('pending');
        $mission->setStartedAt(new \DateTimeImmutable());

        // Ajouter les pouvoirs sélectionnés à la mission
        foreach ($selectedPowers as $power) {
            $mission->addRequiredPower($power);
        }

        $entityManager->persist($mission);
        $entityManager->flush();

        $this->addFlash('success', 'Une nouvelle mission aléatoire a été créée avec ' . count($selectedPowers) . ' pouvoir(s) requis !');
        return $this->redirectToRoute('app_mission_index');
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
