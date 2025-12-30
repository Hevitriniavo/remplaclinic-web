<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request
{
    const CREATED = 0; // A valider
    const IN_PROGRESS = 1; // En cours
    const ARCHIVED = 2; // Archivé

    #[Groups(['request:datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['full'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Groups(['request:datatable'])]
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[Groups(['request:datatable'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[Groups(['request:datatable'])]
    #[ORM\Column(nullable: true)]
    private ?bool $showEndAt = null;

    #[Groups(['request:datatable'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endAt = null;

    #[Groups(['request:datatable'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastSentAt = null;

    #[Groups(['request:datatable'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $applicant = null;

    #[Groups(['full'])]
    #[ORM\ManyToOne]
    private ?Region $region = null;

    #[Groups(['request:datatable'])]
    #[ORM\ManyToOne]
    private ?Speciality $speciality = null;

    #[Groups(['request:datatable'])]
    #[ORM\Column(nullable: true, enumType: RequestType::class)]
    private ?RequestType $requestType = null;

    #[Groups(['full'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remuneration = null;

    #[Groups(['full'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[Groups(['full'])]
    /**
     * @var Collection<int, Speciality>
     */
    #[ORM\ManyToMany(targetEntity: Speciality::class)]
    private Collection $subSpecialities;

    #[Groups(['full'])]
    #[ORM\Column(nullable: true)]
    private ?int $positionCount = null;

    #[Groups(['full'])]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $accomodationIncluded = null;

    #[Groups(['full'])]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $transportCostRefunded = null;

    #[Groups(['full'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $retrocession = null;

    #[Groups(['full'])]
    #[ORM\Column(nullable: true, enumType: RequestReplacementType::class)]
    private ?RequestReplacementType $replacementType = null;

    #[Groups(['full'])]
    /**
     * @var Collection<int, RequestHistory>
     */
    #[ORM\OneToMany(targetEntity: RequestHistory::class, mappedBy: 'request', orphanRemoval: true)]
    private Collection $sentDates;

    #[Groups(['request:datatable'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, RequestResponse>
     */
    #[ORM\OneToMany(targetEntity: RequestResponse::class, mappedBy: 'request', orphanRemoval: true)]
    private Collection $responses;

    #[Groups(['full'])]
    /**
     * @var Collection<int, RequestReason>
     */
    #[ORM\OneToMany(targetEntity: RequestReason::class, mappedBy: 'request', orphanRemoval: true)]
    private Collection $reasons;

    public function __construct()
    {
        $this->subSpecialities = new ArrayCollection();
        $this->sentDates = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->reasons = new ArrayCollection();
    }

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getStartedAtFr(bool $includeTime = true): ?string
    {
        if (empty($this->startedAt)) {
            return '';
        }
        return $this->startedAt->format($includeTime ? 'd/m/Y H:i' : 'd/m/Y');
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function isShowEndAt(): ?bool
    {
        return $this->showEndAt;
    }

    public function setShowEndAt(?bool $showEndAt): static
    {
        $this->showEndAt = $showEndAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function getEndAtFr(bool $includeTime = true): ?string
    {
        if (empty($this->endAt)) {
            return '';
        }
        return $this->endAt->format($includeTime ? 'd/m/Y H:i' : 'd/m/Y');
    }

    public function setEndAt(?\DateTimeInterface $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getLastSentAt(): ?\DateTimeInterface
    {
        return $this->lastSentAt;
    }

    public function setLastSentAt(\DateTimeInterface $lastSentAt): static
    {
        $this->lastSentAt = $lastSentAt;

        return $this;
    }

    public function getApplicant(): ?User
    {
        return $this->applicant;
    }

    public function setApplicant(?User $applicant): static
    {
        $this->applicant = $applicant;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

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

    public function getRequestType(): ?RequestType
    {
        return $this->requestType;
    }

    public function setRequestType(?RequestType $requestType): static
    {
        $this->requestType = $requestType;

        return $this;
    }

    public function getRemuneration(): ?string
    {
        return $this->remuneration;
    }

    public function setRemuneration(?string $remuneration): static
    {
        $this->remuneration = $remuneration;

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

    public function clearSubSpeciality(): static
    {
        $this->subSpecialities = new ArrayCollection();

        return $this;
    }

    public function removeSubSpeciality(Speciality $subSpeciality): static
    {
        $this->subSpecialities->removeElement($subSpeciality);

        return $this;
    }

    public function getPositionCount(): ?int
    {
        return $this->positionCount;
    }

    public function setPositionCount(?int $positionCount): static
    {
        $this->positionCount = $positionCount;

        return $this;
    }

    public function getAccomodationIncluded(): ?int
    {
        return $this->accomodationIncluded;
    }

    public function setAccomodationIncluded(?int $accomodationIncluded): static
    {
        $this->accomodationIncluded = $accomodationIncluded;

        return $this;
    }

    public function getTransportCostRefunded(): ?int
    {
        return $this->transportCostRefunded;
    }

    public function setTransportCostRefunded(?int $transportCostRefunded): static
    {
        $this->transportCostRefunded = $transportCostRefunded;

        return $this;
    }

    public function getRetrocession(): ?string
    {
        return $this->retrocession;
    }

    public function setRetrocession(?string $retrocession): static
    {
        $this->retrocession = $retrocession;

        return $this;
    }

    public function getReplacementType(): ?RequestReplacementType
    {
        return $this->replacementType;
    }

    public function setReplacementType(?RequestReplacementType $replacementType): static
    {
        $this->replacementType = $replacementType;

        return $this;
    }

    /**
     * @return Collection<int, RequestHistory>
     */
    public function getSentDates(): Collection
    {
        return $this->sentDates;
    }

    public function addSentDate(RequestHistory $sentDate): static
    {
        if (!$this->sentDates->contains($sentDate)) {
            $this->sentDates->add($sentDate);
            $sentDate->setRequest($this);
        }

        return $this;
    }

    public function removeSentDate(RequestHistory $sentDate): static
    {
        if ($this->sentDates->removeElement($sentDate)) {
            // set the owning side to null (unless already changed)
            if ($sentDate->getRequest() === $this) {
                $sentDate->setRequest(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, RequestResponse>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(RequestResponse $response): static
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setRequest($this);
        }

        return $this;
    }

    public function removeResponse(RequestResponse $response): static
    {
        if ($this->responses->removeElement($response)) {
            if ($response->getRequest() === $this) {
                $response->setRequest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RequestReason>
     */
    public function getReasons(): Collection
    {
        return $this->reasons;
    }

    public function clearReasons(): static
    {
        $this->reasons = new ArrayCollection();
        return $this;
    }

    public function addReason(RequestReason $reason): static
    {
        if (!$this->reasons->contains($reason)) {
            $this->reasons->add($reason);
            $reason->setRequest($this);
        }

        return $this;
    }

    public function removeReason(RequestReason $reason): static
    {
        if ($this->reasons->removeElement($reason)) {
            // set the owning side to null (unless already changed)
            if ($reason->getRequest() === $this) {
                $reason->setRequest(null);
            }
        }

        return $this;
    }
}
