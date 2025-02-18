<?php

namespace App\Entity;

use App\Repository\UserEstablishmentRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserEstablishmentRepository::class)]
class UserEstablishment
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
    #[ORM\Column(nullable: true)]
    private ?int $bedsCount = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $siteWeb = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?int $consultationCount = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $per = null;

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

    public function getBedsCount(): ?int
    {
        return $this->bedsCount;
    }

    public function setBedsCount(?int $bedsCount): static
    {
        $this->bedsCount = $bedsCount;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): static
    {
        $this->siteWeb = $siteWeb;

        return $this;
    }

    public function getConsultationCount(): ?int
    {
        return $this->consultationCount;
    }

    public function setConsultationCount(?int $consultationCount): static
    {
        $this->consultationCount = $consultationCount;

        return $this;
    }

    public function getPer(): ?string
    {
        return $this->per;
    }

    public function setPer(?string $per): static
    {
        $this->per = $per;

        return $this;
    }
}
