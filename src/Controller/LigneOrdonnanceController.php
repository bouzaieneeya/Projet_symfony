<?php

namespace App\Controller;

use App\Entity\LigneOrdonnance;
use App\Form\LigneOrdonnanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ligne/ordonnance')]
final class LigneOrdonnanceController extends AbstractController
{
    #[Route(name: 'app_ligne_ordonnance_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $ligneOrdonnances = $entityManager
            ->getRepository(LigneOrdonnance::class)
            ->findAll();

        return $this->render('ligne_ordonnance/index.html.twig', [
            'ligne_ordonnances' => $ligneOrdonnances,
        ]);
    }

    #[Route('/new', name: 'app_ligne_ordonnance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ligneOrdonnance = new LigneOrdonnance();
        $form = $this->createForm(LigneOrdonnanceType::class, $ligneOrdonnance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ligneOrdonnance);
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_ordonnance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ligne_ordonnance/new.html.twig', [
            'ligne_ordonnance' => $ligneOrdonnance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_ordonnance_show', methods: ['GET'])]
    public function show(LigneOrdonnance $ligneOrdonnance): Response
    {
        return $this->render('ligne_ordonnance/show.html.twig', [
            'ligne_ordonnance' => $ligneOrdonnance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ligne_ordonnance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LigneOrdonnance $ligneOrdonnance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LigneOrdonnanceType::class, $ligneOrdonnance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_ordonnance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ligne_ordonnance/edit.html.twig', [
            'ligne_ordonnance' => $ligneOrdonnance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_ordonnance_delete', methods: ['POST'])]
    public function delete(Request $request, LigneOrdonnance $ligneOrdonnance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ligneOrdonnance->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ligneOrdonnance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ligne_ordonnance_index', [], Response::HTTP_SEE_OTHER);
    }
}
