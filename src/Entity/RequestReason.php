<?php

namespace App\Entity;

use App\Repository\RequestReasonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RequestReasonRepository::class)]
class RequestReason
{
    const OTHER = 'autre';

    #[Groups(['full'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['full'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    #[Groups(['full'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reasonValue = null;

    #[ORM\ManyToOne(inversedBy: 'reasons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Request $request = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReasonValue(): ?string
    {
        return $this->reasonValue;
    }

    public function setReasonValue(?string $reasonValue): static
    {
        $this->reasonValue = $reasonValue;

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
}
