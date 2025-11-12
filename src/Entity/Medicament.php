<?php
// src/Entity/Medicament.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'medicament')]
class Medicament
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: LigneOrdonnance::class, mappedBy: 'medicament')]
    private Collection $lignesOrdonnance;

    public function __construct()
    {
        $this->lignesOrdonnance = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, LigneOrdonnance>
     */
    public function getLignesOrdonnance(): Collection
    {
        return $this->lignesOrdonnance;
    }

    public function addLignesOrdonnance(LigneOrdonnance $lignesOrdonnance): self
    {
        if (!$this->lignesOrdonnance->contains($lignesOrdonnance)) {
            $this->lignesOrdonnance->add($lignesOrdonnance);
            $lignesOrdonnance->setMedicament($this);
        }
        return $this;
    }

    public function removeLignesOrdonnance(LigneOrdonnance $lignesOrdonnance): self
    {
        if ($this->lignesOrdonnance->removeElement($lignesOrdonnance)) {
            if ($lignesOrdonnance->getMedicament() === $this) {
                $lignesOrdonnance->setMedicament(null);
            }
        }
        return $this;
    }
}