<?php
// src/Entity/Consultation.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'consultation')]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateConsultation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnostic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(targetEntity: Medecin::class, inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Medecin $medecin = null;

    #[ORM\OneToOne(targetEntity: RendezVous::class, inversedBy: 'consultation')]
    #[ORM\JoinColumn(nullable: true)]
    private ?RendezVous $rendezVous = null;

    #[ORM\OneToMany(targetEntity: Ordonnance::class, mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private Collection $ordonnances;

    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: 'consultation', cascade: ['persist', 'remove'])]
    private Collection $paiements;

    public function __construct()
    {
        $this->ordonnances = new ArrayCollection();
        $this->paiements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateConsultation(): ?\DateTimeInterface
    {
        return $this->dateConsultation;
    }

    public function setDateConsultation(\DateTimeInterface $dateConsultation): self
    {
        $this->dateConsultation = $dateConsultation;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(string $diagnostic): self
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): self
    {
        $this->medecin = $medecin;
        return $this;
    }

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(?RendezVous $rendezVous): self
    {
        $this->rendezVous = $rendezVous;
        return $this;
    }

    /**
     * @return Collection<int, Ordonnance>
     */
    public function getOrdonnances(): Collection
    {
        return $this->ordonnances;
    }

    public function addOrdonnance(Ordonnance $ordonnance): self
    {
        if (!$this->ordonnances->contains($ordonnance)) {
            $this->ordonnances->add($ordonnance);
            $ordonnance->setConsultation($this);
        }
        return $this;
    }

    public function removeOrdonnance(Ordonnance $ordonnance): self
    {
        if ($this->ordonnances->removeElement($ordonnance)) {
            if ($ordonnance->getConsultation() === $this) {
                $ordonnance->setConsultation(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): self
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setConsultation($this);
        }
        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            if ($paiement->getConsultation() === $this) {
                $paiement->setConsultation(null);
            }
        }
        return $this;
    }
}