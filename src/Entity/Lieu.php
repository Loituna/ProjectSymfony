<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu

{
    #[Groups("lieu_data")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups("lieu_data")]
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[Groups("lieu_data")]
    #[ORM\Column(length: 80)]
    private ?string $rue = null;

    #[Groups("lieu_data")]
    #[ORM\Column(nullable: true)]
    private ?float $lattitude = null;

    #[Groups("lieu_data")]
    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[Groups("lieu_data")]
    #[ORM\OneToMany(mappedBy: 'lieu', targetEntity: Sortie::class)]
    private Collection $sorties;

    #[Groups("lieu_data")]
    #[ORM\ManyToOne(inversedBy: 'lieux')]
    private ?Ville $ville = null;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
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

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLattitude(): ?float
    {
        return $this->lattitude;
    }

    public function setLattitude(?float $lattitude): self
    {
        $this->lattitude = $lattitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addYe(Sortie $ye): self
    {
        if (!$this->sorties->contains($ye)) {
            $this->sorties->add($ye);
            $ye->setLieu($this);
        }

        return $this;
    }

    public function removeYe(Sortie $ye): self
    {
        if ($this->sorties->removeElement($ye)) {
            // set the owning side to null (unless already changed)
            if ($ye->getLieu() === $this) {
                $ye->setLieu(null);
            }
        }

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }
}
