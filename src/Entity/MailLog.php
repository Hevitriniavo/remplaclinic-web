<?php

namespace App\Entity;

use App\Repository\MailLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MailLogRepository::class)]
class MailLog
{
    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private array $sender = [];

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $target = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cc = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bcc = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private ?bool $html = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[Groups(['full'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body = null;

    #[Groups(['datatable'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Available key:
     *  - name
     *  - email
     * 
     * @return array
     */
    public function getSender(): array
    {
        return $this->sender;
    }

    public function setSender(array $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getCc(): ?string
    {
        return $this->cc;
    }

    public function setCc(?string $cc): static
    {
        $this->cc = $cc;

        return $this;
    }

    public function getBcc(): ?string
    {
        return $this->bcc;
    }

    public function setBcc(?string $bcc): static
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function isHtml(): ?bool
    {
        return $this->html;
    }

    public function setHtml(bool $html): static
    {
        $this->html = $html;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(?string $event): static
    {
        $this->event = $event;

        return $this;
    }
}
