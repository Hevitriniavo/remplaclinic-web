<?php

namespace App\Entity;

use App\Repository\UserAddressRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserAddressRepository::class)]
class UserAddress
{
    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $country = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $locality = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $postal_code = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thoroughfare = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $premise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): static
    {
        $this->locality = $locality;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): static
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getThoroughfare(): ?string
    {
        return $this->thoroughfare;
    }

    public function setThoroughfare(?string $thoroughfare): static
    {
        $this->thoroughfare = $thoroughfare;

        return $this;
    }

    public function getPremise(): ?string
    {
        return $this->premise;
    }

    public function setPremise(?string $premise): static
    {
        $this->premise = $premise;

        return $this;
    }
}
