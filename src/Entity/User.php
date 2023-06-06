<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: "Dieu Irwin te l'interdit ! Pseudo déjà existant !")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Un pseudo est obligatoire !")]
    #[Assert\Length(
        min: 1,
        max: 25,
        minMessage: "Minimum {{ value }} character",
        maxMessage: "Maximum {{ value }} characters"
    )]
    private ?string $pseudo = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
//    #[Assert\NotBlank(message: "Un mots de passe est obligatoire !")]
//    #[Assert\Length(
//        min: 1,
//        max: 25,
//        minMessage: "Minimum {{ value }} character",
//        maxMessage: "Maximum {{ value }} characters"
//    )]
    private ?string $password = null;

    #[Assert\NotBlank(message: "Le nom est obligatoire !")]
    #[Assert\Length(
        min: 1,
        max: 50,
        minMessage: "Minimum {{ value }} character",
        maxMessage: "Maximum {{ value }} characters"
    )]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prenom est obligatoire !")]
    #[Assert\Length(
        min: 1,
        max: 25,
        minMessage: "Minimum {{ value }} character",
        maxMessage: "Maximum {{ value }} characters"
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Une adresse mail est obligatoire !")]
    #[Assert\Email(message: "L'adresse mail n'est pas valide.")]
    private ?string $mail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Campus $campus = null;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $sorties;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class,  cascade: ['remove'])]
    private Collection $sortiesOrga;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sortiesOrga = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {


        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        if ($administrateur){
            $this->setRoles(['ROLE_ADMIN']);
        }else{
            $this->setRoles(['ROLE_USER']);
        }


        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        $this->sorties->removeElement($sorty);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrga(): Collection
    {
        return $this->sortiesOrga;
    }

    public function addSortiesOrga(Sortie $sortiesOrga): self
    {
        if (!$this->sortiesOrga->contains($sortiesOrga)) {
            $this->sortiesOrga->add($sortiesOrga);
            $sortiesOrga->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrga(Sortie $sortiesOrga): self
    {
        if ($this->sortiesOrga->removeElement($sortiesOrga)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrga->getOrganisateur() === $this) {
                $sortiesOrga->setOrganisateur(null);
            }
        }

        return $this;
    }


}
