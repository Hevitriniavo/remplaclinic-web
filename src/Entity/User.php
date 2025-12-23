<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    const ROLE_REPLACEMENT_ID = 4;
    const ROLE_CLINIC_ID = 5;
    const ROLE_DOCTOR_ID = 6;
    const ROLE_DIRECTOR_ID = 7;

    #[Groups(['datatable', 'user:simple'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $ordinaryNumber = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 10)]
    private ?string $civility = null;

    #[Groups(['datatable', 'user:simple'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surname = null;

    #[Groups(['datatable', 'user:simple'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?int $yearOfBirth = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $nationality = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private ?bool $status = null;

    #[Groups(['datatable'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?UserAddress $address = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telephone = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone2 = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fax = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organism = null;

    #[Groups(['datatable'])]
    #[ORM\ManyToOne]
    private ?Speciality $speciality = null;

    #[Groups(['full'])]
    /**
     * @var Collection<int, Speciality>
     */
    #[ORM\ManyToMany(targetEntity: Speciality::class)]
    private Collection $subSpecialities;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?int $yearOfAlternance = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $currentSpeciality = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[Groups(['datatable'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?UserEstablishment $establishment = null;

    #[Groups(['datatable'])]
    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $clinic = null;

    #[Groups(['full', 'user:simple'])]
    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\ManyToMany(targetEntity: UserRole::class)]
    private Collection $roles;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createAt = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userComment = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cv = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diplom = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $licence = null;

    #[Groups(['full'])]
    /**
     * @var Collection<int, Region>
     */
    #[ORM\ManyToMany(targetEntity: Region::class)]
    private Collection $mobilities;

    #[Groups(['datatable'])]
    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserSubscription $subscription = null;

    public function __construct()
    {
        $this->subSpecialities = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->mobilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdinaryNumber(): ?string
    {
        return $this->ordinaryNumber;
    }

    public function setOrdinaryNumber(?string $ordinaryNumber): static
    {
        $this->ordinaryNumber = $ordinaryNumber;

        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(string $civility): static
    {
        $this->civility = $civility;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getYearOfBirth(): ?int
    {
        return $this->yearOfBirth;
    }

    public function setYearOfBirth(?int $yearOfBirth): static
    {
        $this->yearOfBirth = $yearOfBirth;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAddress(): ?UserAddress
    {
        return $this->address;
    }

    public function setAddress(?UserAddress $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getTelephone2(): ?string
    {
        return $this->telephone2;
    }

    public function setTelephone2(?string $telephone2): static
    {
        $this->telephone2 = $telephone2;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): static
    {
        $this->fax = $fax;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getOrganism(): ?string
    {
        return $this->organism;
    }

    public function setOrganism(?string $organism): static
    {
        $this->organism = $organism;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function setSpeciality(?Speciality $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    /**
     * @return Collection<int, Speciality>
     */
    public function getSubSpecialities(): Collection
    {
        return $this->subSpecialities;
    }

    public function addSubSpeciality(Speciality $subSpeciality): static
    {
        if (!$this->subSpecialities->contains($subSpeciality)) {
            $this->subSpecialities->add($subSpeciality);
        }

        return $this;
    }

    public function removeSubSpeciality(Speciality $subSpeciality): static
    {
        $this->subSpecialities->removeElement($subSpeciality);

        return $this;
    }

    public function getYearOfAlternance(): ?int
    {
        return $this->yearOfAlternance;
    }

    public function setYearOfAlternance(?int $yearOfAlternance): static
    {
        $this->yearOfAlternance = $yearOfAlternance;

        return $this;
    }

    public function getCurrentSpeciality(): ?int
    {
        return $this->currentSpeciality;
    }

    public function setCurrentSpeciality(?int $currentSpeciality): static
    {
        $this->currentSpeciality = $currentSpeciality;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getEstablishment(): ?UserEstablishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?UserEstablishment $establishment): static
    {
        $this->establishment = $establishment;

        return $this;
    }

    public function getClinic(): ?self
    {
        return $this->clinic;
    }

    public function setClinic(?self $clinic): static
    {
        $this->clinic = $clinic;

        return $this;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(UserRole $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(UserRole $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function clearRole(): static
    {
        $this->roles = new ArrayCollection();

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(?\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUserComment(): ?string
    {
        return $this->userComment;
    }

    public function setUserComment(?string $userComment): static
    {
        $this->userComment = $userComment;

        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(?string $cv): static
    {
        $this->cv = $cv;

        return $this;
    }

    public function getDiplom(): ?string
    {
        return $this->diplom;
    }

    public function setDiplom(?string $diplom): static
    {
        $this->diplom = $diplom;

        return $this;
    }

    public function getLicence(): ?string
    {
        return $this->licence;
    }

    public function setLicence(?string $licence): static
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * @return Collection<int, Region>
     */
    public function getMobilities(): Collection
    {
        return $this->mobilities;
    }

    public function addMobility(Region $mobility): static
    {
        if (!$this->mobilities->contains($mobility)) {
            $this->mobilities->add($mobility);
        }

        return $this;
    }

    public function removeMobility(Region $mobility): static
    {
        $this->mobilities->removeElement($mobility);

        return $this;
    }

    public function clearMobility(): static
    {
        $this->mobilities = new ArrayCollection();
        
        return $this;
    }

    public function clearSubSpeciality(): static
    {
        $this->subSpecialities = new ArrayCollection();
        
        return $this;
    }

    public function getSubscription(): ?UserSubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?UserSubscription $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }
}
