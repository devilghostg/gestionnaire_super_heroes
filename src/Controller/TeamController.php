<?php

// src/Controller/TeamController.php
namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TeamController extends AbstractController
{
    #[Route('/team/new', name: 'app_team_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist and flush the new team
            $entityManager->persist($team);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_team_index');
        }
    
        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
            'button_label' => 'Créer', // Ajout de la variable
        ]);
    }
    #[Route('/team/{id}/edit', name: 'app_team_edit')]
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
        'button_label' => 'Modifier', // Ajout de la variable pour le mode édition
    ]);
}

    #[Route('/team', name: 'app_team_index')]
    public function index(TeamRepository $teamRepository): Response
    {
        $teams = $teamRepository->findAll();

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'button_label' => 'Créer',
        ]);
    }
}
