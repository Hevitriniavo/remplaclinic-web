<?php

namespace App\Entity;

use App\Repository\SpecialityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SpecialityRepository::class)]
class Speciality
{
    #[Groups(['datatable', 'speciality:simple'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable', 'speciality:simple'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['datatable'])]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'specialities')]
    private ?self $specialityParent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'specialityParent')]
    private Collection $specialities;

    public function __construct()
    {
        $this->specialities = new ArrayCollection();
    }

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

    public function getSpecialityParent(): ?self
    {
        return $this->specialityParent;
    }

    public function setSpecialityParent(?self $specialityParent): static
    {
        $this->specialityParent = $specialityParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSpecialities(): Collection
    {
        return $this->specialities;
    }

    public function addSpeciality(self $speciality): static
    {
        if (!$this->specialities->contains($speciality)) {
            $this->specialities->add($speciality);
            $speciality->setSpecialityParent($this);
        }

        return $this;
    }

    public function removeSpeciality(self $speciality): static
    {
        if ($this->specialities->removeElement($speciality)) {
            if ($speciality->getSpecialityParent() === $this) {
                $speciality->setSpecialityParent(null);
            }
        }

        return $this;
    }
}
