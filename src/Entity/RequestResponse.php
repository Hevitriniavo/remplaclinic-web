<?php

namespace App\Entity;

use App\Repository\RequestResponseRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestResponseRepository::class)]
class RequestResponse
{
    const EN_COURS = 0;
    const ACCEPTE = 1;
    const PLUS_D_INFOS = 2;
    const EXCLU = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Request $request = null;

    public function __construct()
    {
        $this->createAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function getApplicantName(): string
    {
        $role = $this->request->getApplicant()->getRole();
        
        if (!is_null($role)) {
            if ($role->getId() == 5) {
                return $this->request->getApplicant()->getEstablishment()->getName();
            }

            if ($role->getId() == 6) {
                return 'Cabinet Médical';
            }
        }

        return '';
    }

    public function getStatusAsText(): string
    {
        $statuses = ["En cours", "Candidature en cours", "Demande d'informations"];
        
        if (array_key_exists($this->getStatus(), $statuses)) {
            return $statuses[$this->getStatus()];
        }

        return '';
    }

    public function getApplicantStatusAsText(): string
    {
        $statuses = ["En cours", "Accepté", "Demande d'informations complémentaires"];
        
        if (array_key_exists($this->getStatus(), $statuses)) {
            return $statuses[$this->getStatus()];
        }

        return '';
    }
}
