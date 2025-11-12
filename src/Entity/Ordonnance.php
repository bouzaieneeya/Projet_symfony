<?php
// src/Entity/Ordonnance.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ordonnance')]
class Ordonnance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateEmission = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaires = null;

    #[ORM\ManyToOne(targetEntity: Consultation::class, inversedBy: 'ordonnances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Consultation $consultation = null;

    #[ORM\OneToMany(targetEntity: LigneOrdonnance::class, mappedBy: 'ordonnance', cascade: ['persist', 'remove'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateEmission(): ?\DateTimeInterface
    {
        return $this->dateEmission;
    }

    public function setDateEmission(\DateTimeInterface $dateEmission): self
    {
        $this->dateEmission = $dateEmission;
        return $this;
    }

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): self
    {
        $this->commentaires = $commentaires;
        return $this;
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(?Consultation $consultation): self
    {
        $this->consultation = $consultation;
        return $this;
    }

    /**
     * @return Collection<int, LigneOrdonnance>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneOrdonnance $ligne): self
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setOrdonnance($this);
        }
        return $this;
    }

    public function removeLigne(LigneOrdonnance $ligne): self
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getOrdonnance() === $this) {
                $ligne->setOrdonnance(null);
            }
        }
        return $this;
    }
}