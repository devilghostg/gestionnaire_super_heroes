<?php

// src/Controller/PowerController.php

namespace App\Controller;

use App\Entity\Power;
use App\Form\PowerType;
use App\Repository\PowerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class PowerController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PowerRepository $powerRepository;

    // Injection de l'EntityManager et du PowerRepository
    public function __construct(EntityManagerInterface $entityManager, PowerRepository $powerRepository)
    {
        $this->entityManager = $entityManager;
        $this->powerRepository = $powerRepository;
    }

    // Liste des pouvoirs
    #[Route('/powers', name: 'power_index')]
    public function index(): Response
    {
        // Récupération de tous les pouvoirs
        $powers = $this->powerRepository->findAll();

        // Rendu de la vue avec la liste des pouvoirs
        return $this->render('power/index.html.twig', [
            'powers' => $powers,
        ]);
    }

    // Création d'un nouveau pouvoir
    #[Route('/powers/new', name: 'power_new')]
    public function new(Request $request): Response
    {
        $power = new Power();
        $form = $this->createForm(PowerType::class, $power);

        // Traitement de la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($power);
            $this->entityManager->flush();

            // Redirection vers la page de détails du nouveau pouvoir
            return $this->redirectToRoute('power_show', ['id' => $power->getId()]);
        }

        // Rendu de la vue pour créer un pouvoir
        return $this->render('power/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Affichage des détails d'un pouvoir
    #[Route('/powers/{id}', name: 'power_show')]
    public function show(Power $power): Response
    {
        // Rendu de la vue pour afficher un pouvoir spécifique
        return $this->render('power/show.html.twig', [
            'power' => $power,
        ]);
    }

    // Modification d'un pouvoir existant
    #[Route('/powers/{id}/edit', name: 'power_edit')]
    public function edit(Request $request, Power $power): Response
    {
        // Création du formulaire pour éditer un pouvoir
        $form = $this->createForm(PowerType::class, $power);

        // Traitement de la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarde des modifications
            $this->entityManager->flush();

            // Redirection vers la page de détails du pouvoir modifié
            return $this->redirectToRoute('power_show', ['id' => $power->getId()]);
        }

        // Rendu de la vue pour modifier un pouvoir
        return $this->render('power/edit.html.twig', [
            'form' => $form->createView(),
            'power' => $power,
        ]);
    }

    // Suppression d'un pouvoir
    #[Route('/powers/{id}/delete', name: 'power_delete')]
    public function delete(Request $request, Power $power): Response
    {
        // Vérification que la requête est une suppression (par exemple un formulaire POST)
        if ($this->isCsrfTokenValid('delete'.$power->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($power);
            $this->entityManager->flush();
        }

        // Redirection vers la liste des pouvoirs après suppression
        return $this->redirectToRoute('power_index');
    }
    
}