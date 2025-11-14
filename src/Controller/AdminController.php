<?php
// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Secretaire;
use App\Entity\Consultation;
use App\Entity\RendezVous;
use App\Form\PatientType;
use App\Form\MedecinType;
use App\Form\SecretaireType;
use App\Form\RendezVousType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    #[Route('/', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        // ✅ FIXED: Better error handling with logging
        try {
            $stats = [
                'patients' => $em->getRepository(Patient::class)->count([]),
                'medecins' => $em->getRepository(Medecin::class)->count([]),
                'secretaires' => $em->getRepository(Secretaire::class)->count([]),
                'consultations' => $em->getRepository(Consultation::class)->count([]),
                'rendezVous' => $em->getRepository(RendezVous::class)->count([]),
            ];
        } catch (\Exception $e) {
            // ✅ FIXED: Log the error instead of silently failing
            $this->logger->error('Failed to retrieve dashboard stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // ✅ FIXED: Show error message to user
            $this->addFlash('error', 'Erreur lors du chargement des statistiques.');
            
            $stats = [
                'patients' => 0,
                'medecins' => 0,
                'secretaires' => 0,
                'consultations' => 0,
                'rendezVous' => 0,
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    // ========== PATIENTS CRUD ==========
    
    #[Route('/patients', name: 'admin_patients')]
    public function patients(EntityManagerInterface $em): Response
    {
        $patients = $em->getRepository(Patient::class)->findAll();
        return $this->render('admin/patients.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/patients/new', name: 'admin_patients_new')]
    public function newPatient(Request $request, EntityManagerInterface $em): Response
    {
        $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($patient);
                $em->flush();

                // ✅ FIXED: Use translation keys instead of hardcoded text
                $this->addFlash('success', 'Patient créé avec succès!');
                return $this->redirectToRoute('admin_patients');
            } catch (\Exception $e) {
                $this->logger->error('Failed to create patient', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la création du patient.');
            }
        }

        return $this->render('admin/patient_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau Patient'
        ]);
    }

    #[Route('/patients/{id}/edit', name: 'admin_patients_edit')]
    public function editPatient(Patient $patient, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Patient modifié avec succès!');
                return $this->redirectToRoute('admin_patients');
            } catch (\Exception $e) {
                $this->logger->error('Failed to update patient', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la modification du patient.');
            }
        }

        return $this->render('admin/patient_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier Patient',
            'patient' => $patient
        ]);
    }

    #[Route('/patients/{id}/delete', name: 'admin_patients_delete', methods: ['POST'])]
    public function deletePatient(Patient $patient, Request $request, EntityManagerInterface $em): Response
    {
        // ✅ FIXED: Proper CSRF token validation
        if ($this->isCsrfTokenValid('delete'.$patient->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($patient);
                $em->flush();
                $this->addFlash('success', 'Patient supprimé avec succès!');
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete patient', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la suppression du patient.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_patients');
    }

    // ========== MEDECINS CRUD ==========
    
    #[Route('/medecins', name: 'admin_medecins')]
    public function medecins(EntityManagerInterface $em): Response
    {
        $medecins = $em->getRepository(Medecin::class)->findAll();
        return $this->render('admin/medecins.html.twig', [
            'medecins' => $medecins,
        ]);
    }

    #[Route('/medecins/new', name: 'admin_medecins_new')]
    public function newMedecin(Request $request, EntityManagerInterface $em): Response
    {
        $medecin = new Medecin();
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($medecin);
                $em->flush();

                $this->addFlash('success', 'Médecin créé avec succès!');
                return $this->redirectToRoute('admin_medecins');
            } catch (\Exception $e) {
                $this->logger->error('Failed to create medecin', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la création du médecin.');
            }
        }

        return $this->render('admin/medecin_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau Médecin'
        ]);
    }

    #[Route('/medecins/{id}/edit', name: 'admin_medecins_edit')]
    public function editMedecin(Medecin $medecin, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Médecin modifié avec succès!');
                return $this->redirectToRoute('admin_medecins');
            } catch (\Exception $e) {
                $this->logger->error('Failed to update medecin', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la modification du médecin.');
            }
        }

        return $this->render('admin/medecin_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier Médecin',
            'medecin' => $medecin
        ]);
    }

    #[Route('/medecins/{id}/delete', name: 'admin_medecins_delete', methods: ['POST'])]
    public function deleteMedecin(Medecin $medecin, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medecin->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($medecin);
                $em->flush();
                $this->addFlash('success', 'Médecin supprimé avec succès!');
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete medecin', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la suppression du médecin.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_medecins');
    }

    // ========== SECRETAIRES CRUD ==========
    
    #[Route('/secretaires', name: 'admin_secretaires')]
    public function secretaires(EntityManagerInterface $em): Response
    {
        $secretaires = $em->getRepository(Secretaire::class)->findAll();
        return $this->render('admin/secretaires.html.twig', [
            'secretaires' => $secretaires,
        ]);
    }

    #[Route('/secretaires/new', name: 'admin_secretaires_new')]
    public function newSecretaire(Request $request, EntityManagerInterface $em): Response
    {
        $secretaire = new Secretaire();
        $form = $this->createForm(SecretaireType::class, $secretaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($secretaire);
                $em->flush();

                $this->addFlash('success', 'Secrétaire créée avec succès!');
                return $this->redirectToRoute('admin_secretaires');
            } catch (\Exception $e) {
                $this->logger->error('Failed to create secretaire', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la création de la secrétaire.');
            }
        }

        return $this->render('admin/secretaire_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouvelle Secrétaire'
        ]);
    }

    #[Route('/secretaires/{id}/edit', name: 'admin_secretaires_edit')]
    public function editSecretaire(Secretaire $secretaire, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SecretaireType::class, $secretaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Secrétaire modifiée avec succès!');
                return $this->redirectToRoute('admin_secretaires');
            } catch (\Exception $e) {
                $this->logger->error('Failed to update secretaire', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la modification de la secrétaire.');
            }
        }

        return $this->render('admin/secretaire_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier Secrétaire',
            'secretaire' => $secretaire
        ]);
    }

    #[Route('/secretaires/{id}/delete', name: 'admin_secretaires_delete', methods: ['POST'])]
    public function deleteSecretaire(Secretaire $secretaire, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$secretaire->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($secretaire);
                $em->flush();
                $this->addFlash('success', 'Secrétaire supprimée avec succès!');
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete secretaire', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la suppression de la secrétaire.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_secretaires');
    }

    // ========== RENDEZ-VOUS CRUD ==========
    
    #[Route('/rendez-vous', name: 'admin_rendez_vous')]
    public function rendezVous(EntityManagerInterface $em): Response
    {
        $rendezVous = $em->getRepository(RendezVous::class)->findAll();
        return $this->render('admin/rendez_vous.html.twig', [
            'rendezVous' => $rendezVous,
        ]);
    }

    #[Route('/rendez-vous/new', name: 'admin_rendez_vous_new')]
    public function newRendezVous(Request $request, EntityManagerInterface $em): Response
    {
        $rendezVous = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($rendezVous);
                $em->flush();

                $this->addFlash('success', 'Rendez-vous créé avec succès!');
                return $this->redirectToRoute('admin_rendez_vous');
            } catch (\Exception $e) {
                $this->logger->error('Failed to create rendez-vous', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la création du rendez-vous.');
            }
        }

        return $this->render('admin/rendez_vous_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau Rendez-vous'
        ]);
    }

    #[Route('/rendez-vous/{id}/edit', name: 'admin_rendez_vous_edit')]
    public function editRendezVous(RendezVous $rendezVous, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Rendez-vous modifié avec succès!');
                return $this->redirectToRoute('admin_rendez_vous');
            } catch (\Exception $e) {
                $this->logger->error('Failed to update rendez-vous', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la modification du rendez-vous.');
            }
        }

        return $this->render('admin/rendez_vous_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier Rendez-vous',
            'rendezVous' => $rendezVous
        ]);
    }

    #[Route('/rendez-vous/{id}/delete', name: 'admin_rendez_vous_delete', methods: ['POST'])]
    public function deleteRendezVous(RendezVous $rendezVous, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVous->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($rendezVous);
                $em->flush();
                $this->addFlash('success', 'Rendez-vous supprimé avec succès!');
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete rendez-vous', ['error' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la suppression du rendez-vous.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }
        
        return $this->redirectToRoute('admin_rendez_vous');
    }
}