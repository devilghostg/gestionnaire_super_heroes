<?php

namespace App\Controller;

use App\Entity\SuperHero;
use App\Form\SuperHeroType;
use App\Repository\PowerRepository;
use App\Repository\SuperHeroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/superhero')]
final class SuperHeroController extends AbstractController{
    #[Route(name: 'app_super_hero_index', methods: ['GET'])]
    public function index(SuperHeroRepository $superHeroRepository): Response
    {
        return $this->render('super_hero/index.html.twig', [
            'super_heroes' => $superHeroRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_super_hero_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $superHero = new SuperHero();
        $form = $this->createForm(SuperHeroType::class, $superHero);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($superHero);
            $entityManager->flush();

            return $this->redirectToRoute('app_super_hero_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('super_hero/new.html.twig', [
            'super_hero' => $superHero,
            'form' => $form,
            'button_label' => 'Enregistrer',
        ]);
    }

    #[Route('/{id}', name: 'app_super_hero_show', methods: ['GET'])]
    public function show(SuperHero $superHero): Response
    {
        return $this->render('super_hero/show.html.twig', [
            'super_hero' => $superHero,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_super_hero_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        SuperHero $superHero, 
        EntityManagerInterface $entityManager, 
        PowerRepository $powerRepository
    ): Response {
        $form = $this->createForm(SuperHeroType::class, $superHero);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_super_hero_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Récupérer tous les pouvoirs pour les transmettre à la vue
        $allPowers = $powerRepository->findAll();
    
        return $this->render('super_hero/edit.html.twig', [
            'super_hero' => $superHero,
            'form' => $form,
            'button_label' => $superHero->getId() ? 'Mettre à jour' : 'Enregistrer',
            'all_powers' => $allPowers, // Passer la variable à la vue
        ]);
    }

    #[Route('/{id}', name: 'app_super_hero_delete', methods: ['POST'])]
    public function delete(Request $request, SuperHero $superHero, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$superHero->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($superHero);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_super_hero_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/add-powers', name: 'super_hero_add_powers', methods: ['GET', 'POST'])]
    public function addPowers(Request $request, SuperHero $superHero, EntityManagerInterface $entityManager): Response
    {
        // Exemple : $powers = $request->request->get('powers');

        // $superHero->addPower($somePower);

        // Sauvegarder en base
        $entityManager->persist($superHero);
        $entityManager->flush();

        return $this->redirectToRoute('app_super_hero_show', ['id' => $superHero->getId()]);
    }
}
