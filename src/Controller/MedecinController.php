<?php
// src/Controller/MedecinController.php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\RendezVous;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/medecin')]
#[IsGranted('ROLE_MEDECIN')]
class MedecinController extends AbstractController
{
    #[Route('/', name: 'medecin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $medecin = $user->getMedecin();

        $rendezVous = $em->getRepository(RendezVous::class)
            ->findBy(['medecin' => $medecin], ['dateRdv' => 'ASC']);

        $consultations = $em->getRepository(Consultation::class)
            ->findBy(['medecin' => $medecin], ['dateConsultation' => 'DESC'], 10);

        return $this->render('medecin/dashboard.html.twig', [
            'medecin' => $medecin,
            'rendezVous' => $rendezVous,
            'consultations' => $consultations,
        ]);
    }

    #[Route('/consultations', name: 'medecin_consultations')]
    public function consultations(EntityManagerInterface $em): Response
    {
        $medecin = $this->getUser()->getMedecin();
        $consultations = $em->getRepository(Consultation::class)
            ->findBy(['medecin' => $medecin], ['dateConsultation' => 'DESC']);

        return $this->render('medecin/consultations.html.twig', [
            'consultations' => $consultations,
        ]);
    }

    #[Route('/rendez-vous', name: 'medecin_rendez_vous')]
    public function rendezVous(EntityManagerInterface $em): Response
    {
        $medecin = $this->getUser()->getMedecin();
        $rendezVous = $em->getRepository(RendezVous::class)
            ->findBy(['medecin' => $medecin], ['dateRdv' => 'ASC']);

        return $this->render('medecin/rendez_vous.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }
}