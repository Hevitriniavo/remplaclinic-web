<?php

namespace App\Entity;

use App\Repository\UserSubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserSubscriptionRepository::class)]
class UserSubscription
{
    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endAt = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?bool $status = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?bool $endNotification = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?int $installationCount = 0;

    #[ORM\OneToOne(mappedBy: 'subscription', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isEndNotification(): ?bool
    {
        return $this->endNotification;
    }

    public function setEndNotification(?bool $endNotification): static
    {
        $this->endNotification = $endNotification;

        return $this;
    }

    public function getInstallationCount(): ?int
    {
        return $this->installationCount;
    }

    public function setInstallationCount(?int $installationCount): static
    {
        $this->installationCount = $installationCount;

        return $this;
    }

    public function decrementInstallationCount(): static
    {
        if (!is_null($this->installationCount)) {
            $this->installationCount--;
        }
        
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setSubscription(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getSubscription() !== $this) {
            $user->setSubscription($this);
        }

        $this->user = $user;

        return $this;
    }
}
