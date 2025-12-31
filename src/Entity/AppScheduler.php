<?php

namespace App\Entity;

use App\Repository\AppSchedulerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AppSchedulerRepository::class)]
class AppScheduler
{
    #[Groups(['datatable'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 255)]
    private ?string $script = null;

    #[Groups(['datatable'])]
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $options = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 30)]
    private ?string $time = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $executedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $output = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getScript(): ?string
    {
        return $this->script;
    }

    public function setScript(string $script): static
    {
        $this->script = $script;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getExecutedAt(): ?\DateTimeImmutable
    {
        return $this->executedAt;
    }

    public function setExecutedAt(?\DateTimeImmutable $executedAt): static
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(?string $output): static
    {
        $this->output = $output;

        return $this;
    }
}
