<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)*
 * @UniqueEntity(fields={"email"}, message="Il y a déjà un compte avec cette email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * 
     * @Groups("user_read")
     * @Groups("reservation_read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=49, unique=true)
     * @Groups("user_read")
     * @Assert\Email(
     *     message="Veuillez saisir un email valide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * 
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="user")
     */
    private $reservations;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"user_read"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"user_read"})
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups({"user_read"})
     * @Assert\Regex(
     *     pattern     = "/^[A-Za-zÀ-úœ'\-\s]+$/i",
     *     htmlPattern = "[A-Za-zÀ-úœ'\-\s]+",
     *     message="Veuillez saisir un prénom valide."
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Le nom doit avoir au minimum {{ limit }} caractères",
     *      maxMessage = "Le nom ne peut avoir plus de {{ limit }} caractères"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_read"})
     * 
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"user_read"})
     * @Assert\Regex(
     *     pattern="/\d/",
     *     match=true,
     *     message="Veuillez saisir un numéro de téléphone avec uniquement des chiffres"
     * )
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"user_read"})
     * @Assert\Regex(
     *     pattern     = "/^[A-Za-zÀ-úœ'\-\s]+$/i",
     *     htmlPattern = "[A-Za-zÀ-úœ'\-\s]+",
     *     message="Veuillez saisir un prénom valide."
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Le prénom doit avoir au minimum {{ limit }} caractères",
     *      maxMessage = "Le prénom ne peut avoir plus de {{ limit }} caractères"
     * )
     */
    private $nameFirst;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getNameFirst(): ?string
    {
        return $this->nameFirst;
    }

    public function setNameFirst(string $nameFirst): self
    {
        $this->nameFirst = $nameFirst;

        return $this;
    }
}
