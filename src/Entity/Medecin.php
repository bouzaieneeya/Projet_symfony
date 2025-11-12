<?php
// src/Entity/Medecin.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'medecin')]
class Medecin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $specialite = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'medecin', cascade: ['persist', 'remove'])]
    private Collection $rendezVous;

    #[ORM\OneToMany(targetEntity: Consultation::class, mappedBy: 'medecin', cascade: ['persist', 'remove'])]
    private Collection $consultations;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
        $this->consultations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVous(): Collection
    {
        return $this->rendezVous;
    }

    public function addRendezVous(RendezVous $rendezVous): self
    {
        if (!$this->rendezVous->contains($rendezVous)) {
            $this->rendezVous->add($rendezVous);
            $rendezVous->setMedecin($this);
        }
        return $this;
    }

    public function removeRendezVous(RendezVous $rendezVous): self
    {
        if ($this->rendezVous->removeElement($rendezVous)) {
            if ($rendezVous->getMedecin() === $this) {
                $rendezVous->setMedecin(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    public function addConsultation(Consultation $consultation): self
    {
        if (!$this->consultations->contains($consultation)) {
            $this->consultations->add($consultation);
            $consultation->setMedecin($this);
        }
        return $this;
    }

    public function removeConsultation(Consultation $consultation): self
    {
        if ($this->consultations->removeElement($consultation)) {
            if ($consultation->getMedecin() === $this) {
                $consultation->setMedecin(null);
            }
        }
        return $this;
    }
}