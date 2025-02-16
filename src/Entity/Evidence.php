<?php

namespace App\Entity;

use App\Repository\EvidenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EvidenceRepository::class)]
class Evidence
{
    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true, name: 'speciality_name')]
    private ?string $specialityName = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true, name: 'clinic_name')]
    private ?string $clinicName = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSpecialityName(): ?string
    {
        return $this->specialityName;
    }

    public function setSpecialityName(?string $specialityName): static
    {
        $this->specialityName = $specialityName;

        return $this;
    }

    public function getClinicName(): ?string
    {
        return $this->clinicName;
    }

    public function setClinicName(?string $clinicName): static
    {
        $this->clinicName = $clinicName;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }
}
