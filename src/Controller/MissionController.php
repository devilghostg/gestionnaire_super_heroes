<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\SuperHero;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use App\Repository\SuperHeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        SuperHero $hero,
        EntityManagerInterface $entityManager
    ): Response {
        if ($mission->getStatus() !== 'pending') {
            $this->addFlash('error', 'Cette mission n\'est pas disponible');
            return $this->redirectToRoute('app_mission_index');
        }

        $mission->setSuperHero($hero);
        $mission->setStatus('in_progress');
        
        $entityManager->flush();

        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
    }

    #[Route('/{id}/complete', name: 'app_mission_complete', methods: ['POST'])]
    public function complete(
        Mission $mission,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($mission->getStatus() !== 'in_progress') {
            $this->addFlash('error', 'Cette mission ne peut pas être terminée');
            return $this->redirectToRoute('app_mission_index');
        }

        $completedAt = new \DateTimeImmutable();
        $timeTaken = $completedAt->getTimestamp() - $mission->getStartedAt()->getTimestamp();
        
        // Calcul du score basé sur le temps pris et la difficulté
        $score = $this->calculateScore($mission, $timeTaken);
        
        $mission->setStatus('completed');
        $mission->setCompletedAt($completedAt);
        $mission->setScore($score);
        $mission->setResult($request->request->get('result', 'Mission accomplie !'));
        
        $entityManager->flush();

        return $this->redirectToRoute('app_mission_show', ['id' => $mission->getId()]);
    }

    private function calculateScore(Mission $mission, int $timeTaken): int
    {
        // Score de base basé sur la difficulté (1-5) * 1000
        $baseScore = $mission->getDifficulty() * 1000;
        
        // Bonus si terminé avant la limite de temps
        $timeBonus = 0;
        if ($timeTaken < $mission->getTimeLimit()) {
            $timeBonus = ($mission->getTimeLimit() - $timeTaken) * 10;
        }
        
        // Malus si dépassement du temps
        $timePenalty = 0;
        if ($timeTaken > $mission->getTimeLimit()) {
            $timePenalty = ($timeTaken - $mission->getTimeLimit()) * 5;
        }
        
        // Score final
        return max(0, $baseScore + $timeBonus - $timePenalty);
    }
}
