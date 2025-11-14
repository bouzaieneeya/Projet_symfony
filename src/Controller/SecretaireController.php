<?php
// src/Controller/SecretaireController.php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\RendezVous;
use App\Form\PatientType;
use App\Form\RendezVousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/secretaire')]
#[IsGranted('ROLE_SECRETAIRE')]
class SecretaireController extends AbstractController
{
    #[Route('/', name: 'secretaire_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        try {
            // Récupérer les statistiques pour le dashboard
            $stats = [
                'patients' => $em->getRepository(Patient::class)->count([]),
                'rendezVousAujourdhui' => $em->getRepository(RendezVous::class)
                    ->createQueryBuilder('r')
                    ->where('r.dateRdv = :today')
                    ->setParameter('today', new \DateTime())
                    ->select('COUNT(r.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'rendezVousEnAttente' => $em->getRepository(RendezVous::class)
                    ->count(['statut' => 'en attente']),
            ];
        } catch (\Exception $e) {
            $stats = [
                'patients' => 0,
                'rendezVousAujourdhui' => 0,
                'rendezVousEnAttente' => 0,
            ];
        }

        return $this->render('secretaire/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    // ========== PATIENTS CRUD ==========
    
    #[Route('/patients', name: 'secretaire_patients')]
    public function patients(EntityManagerInterface $em): Response
    {
        $patients = $em->getRepository(Patient::class)->findAll();
        return $this->render('secretaire/patients.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/patients/new', name: 'secretaire_patients_new')]
    public function newPatient(Request $request, EntityManagerInterface $em): Response
    {
        $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($patient);
            $em->flush();

            $this->addFlash('success', 'Patient créé avec succès!');
            return $this->redirectToRoute('secretaire_patients');
        }

        return $this->render('secretaire/patient_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau Patient'
        ]);
    }

    #[Route('/patients/{id}/edit', name: 'secretaire_patients_edit')]
    public function editPatient(Patient $patient, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Patient modifié avec succès!');
            return $this->redirectToRoute('secretaire_patients');
        }

        return $this->render('secretaire/patient_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier Patient',
            'patient' => $patient
        ]);
    }

    #[Route('/patients/{id}/delete', name: 'secretaire_patients_delete', methods: ['POST'])]
    public function deletePatient(Patient $patient, EntityManagerInterface $em): Response
    {
        $em->remove($patient);
        $em->flush();
        $this->addFlash('success', 'Patient supprimé avec succès!');
        return $this->redirectToRoute('secretaire_patients');
    }

    // ========== RENDEZ-VOUS CRUD ==========
    
    #[Route('/rendez-vous', name: 'secretaire_rendez_vous')]
    public function rendezVous(EntityManagerInterface $em): Response
    {
        $rendezVous = $em->getRepository(RendezVous::class)->findAll();
        return $this->render('secretaire/rendez_vous.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/rendez-vous/new', name: 'secretaire_rendez_vous_new')]
    public function newRendezVous(Request $request, EntityManagerInterface $em): Response
    {
        $rendezVous = new RendezVous();
        
        // Auto-assigner la secrétaire connectée
        $user = $this->getUser();
        if ($user && $user->getSecretaire()) {
            $rendezVous->setSecretaire($user->getSecretaire());
        }
        
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($rendezVous);
            $em->flush();

            $this->addFlash('success', 'Rendez-vous créé avec succès!');
            return $this->redirectToRoute('secretaire_rendez_vous');
        }

        return $this->render('secretaire/rendez_vous_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau Rendez-vous'
        ]);
    }

    #[Route('/rendez-vous/{id}/edit', name: 'secretaire_rendez_vous_edit')]
    public function editRendezVous(RendezVous $rendezVous, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Rendez-vous modifié avec succès!');
            return $this->redirectToRoute('secretaire_rendez_vous');
        }

        return $this->render('secretaire/rendez_vous_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier Rendez-vous',
            'rendezVous' => $rendezVous
        ]);
    }

    #[Route('/rendez-vous/{id}/delete', name: 'secretaire_rendez_vous_delete', methods: ['POST'])]
    public function deleteRendezVous(RendezVous $rendezVous, EntityManagerInterface $em): Response
    {
        $em->remove($rendezVous);
        $em->flush();
        $this->addFlash('success', 'Rendez-vous supprimé avec succès!');
        return $this->redirectToRoute('secretaire_rendez_vous');
    }

    #[Route('/rendez-vous/{id}/confirmer', name: 'secretaire_rendez_vous_confirmer', methods: ['POST'])]
    public function confirmerRendezVous(RendezVous $rendezVous, EntityManagerInterface $em): Response
    {
        $rendezVous->setStatut('confirmé');
        $em->flush();
        $this->addFlash('success', 'Rendez-vous confirmé avec succès!');
        return $this->redirectToRoute('secretaire_rendez_vous');
    }

    #[Route('/rendez-vous/{id}/annuler', name: 'secretaire_rendez_vous_annuler', methods: ['POST'])]
    public function annulerRendezVous(RendezVous $rendezVous, EntityManagerInterface $em): Response
    {
        $rendezVous->setStatut('annulé');
        $em->flush();
        $this->addFlash('warning', 'Rendez-vous annulé.');
        return $this->redirectToRoute('secretaire_rendez_vous');
    }

    // ========== LISTE DES MÉDECINS (lecture seule) ==========
    
    #[Route('/medecins', name: 'secretaire_medecins')]
    public function medecins(EntityManagerInterface $em): Response
    {
        $medecins = $em->getRepository(Medecin::class)->findAll();
        return $this->render('secretaire/medecins.html.twig', [
            'medecins' => $medecins,
        ]);
    }
}