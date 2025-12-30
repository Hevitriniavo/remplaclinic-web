<?php

namespace App\Entity;

use App\Repository\AdminEmailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdminEmailRepository::class)]
class AdminEmail
{
    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $events = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private ?bool $status = null;

    // #[Groups(['datatable'])]
    // #[ORM\Column]
    // private ?bool $visible = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getEvents(): ?array
    {
        return $this->events;
    }

    public function setEvents(?array $events): static
    {
        $this->events = $events;

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
}
