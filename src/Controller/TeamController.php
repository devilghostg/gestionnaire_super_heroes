<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use App\Repository\SuperHeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/team')]
class TeamController extends AbstractController
{
    #[Route('/', name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findAll();

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_team_index');
        }
    
        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Créer',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_team_index');
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Modifier',
            'team' => $team,
        ]);
    }

    #[Route('/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_index');
    }

    #[Route('/generate-random', name: 'app_team_generate_random', methods: ['POST'])]
    public function generateRandom(
        EntityManagerInterface $entityManager,
        SuperHeroRepository $superHeroRepository
    ): Response {
        $teamData = [
            [
                'name' => 'Les Vengeurs',
                'description' => 'Une équipe d\'élite de super-héros rassemblés pour défendre la Terre contre les menaces les plus dangereuses.',
                'size' => [3, 5]
            ],
            [
                'name' => 'La Ligue des Justiciers',
                'description' => 'Les plus grands héros unis pour protéger l\'humanité et maintenir la paix dans le monde.',
                'size' => [3, 5]
            ],
            [
                'name' => 'Les X-Men',
                'description' => 'Un groupe de mutants qui utilisent leurs pouvoirs extraordinaires pour protéger un monde qui les craint.',
                'size' => [3, 4]
            ],
            [
                'name' => 'Les Quatre Fantastiques',
                'description' => 'Une famille de super-héros qui a obtenu des pouvoirs cosmiques lors d\'une expérience scientifique.',
                'size' => [2, 4]
            ],
            [
                'name' => 'Les Gardiens de la Galaxie',
                'description' => 'Un groupe improbable d\'aventuriers cosmiques qui protègent l\'univers des menaces intergalactiques.',
                'size' => [3, 5]
            ]
        ];

        // Sélectionner une équipe au hasard
        $teamInfo = $teamData[array_rand($teamData)];
        
        // Créer la nouvelle équipe
        $team = new Team();
        $team->setName($teamInfo['name']);
        $team->setDescription($teamInfo['description']);

        // Récupérer tous les héros disponibles
        $availableHeroes = $superHeroRepository->findAll();
        
        if (empty($availableHeroes)) {
            $this->addFlash('error', 'Aucun héros disponible pour créer une équipe.');
            return $this->redirectToRoute('app_team_index');
        }

        // Mélanger les héros et en sélectionner un nombre aléatoire
        shuffle($availableHeroes);
        $teamSize = rand($teamInfo['size'][0], min($teamInfo['size'][1], count($availableHeroes)));
        
        // Ajouter les héros à l'équipe
        for ($i = 0; $i < $teamSize; $i++) {
            $team->addSuperHero($availableHeroes[$i]);
        }

        $entityManager->persist($team);
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'équipe "%s" a été créée avec %d héros !', $team->getName(), $teamSize));
        return $this->redirectToRoute('app_team_index');
    }
}
