<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{

    const CONTACT_UNKNOWN = 86;
    const CONTACT_DEFAULT = 98;
    const CONTACT_ASSISTANCE = 992;
    const CONTACT_OUVERTURE_COMPTE = 1030;
    const CONTACT_INSTAL_CLINIC = 1085;

    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private ?int $contact_type = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remote_addr = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?int $user_id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surname = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[Groups(['full'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true, enumType: ContactObject::class)]
    private ?array $object = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $submitted_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContactType(): ?int
    {
        return $this->contact_type;
    }

    public function setContactType(int $contact_type): static
    {
        $this->contact_type = $contact_type;

        return $this;
    }

    public function getRemoteAddr(): ?string
    {
        return $this->remote_addr;
    }

    public function setRemoteAddr(?string $remote_addr): static
    {
        $this->remote_addr = $remote_addr;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): static
    {
        $this->user_id = $user_id;

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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): static
    {
        $this->surname = $surname;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return ContactObject[]|null
     */
    public function getObject(): ?array
    {
        return $this->object;
    }

    public function setObject(?array $object): static
    {
        $this->object = $object;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submitted_at;
    }

    public function setSubmittedAt(\DateTimeImmutable $submitted_at): static
    {
        $this->submitted_at = $submitted_at;

        return $this;
    }
}
