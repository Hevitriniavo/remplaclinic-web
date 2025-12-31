<?php

namespace App\Entity;

use App\Repository\AppImportationScriptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AppImportationScriptRepository::class)]
class AppImportationScript
{
    const CREATED = 0;
    const STARTED = 1;
    const FAILED = 2;
    const SUCCESS = 3;

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
    #[ORM\Column]
    private ?int $status = null;

    #[Groups(['datatable'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $lastId = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?int $lastCount = null;

    #[Groups(['datatable'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $executedAt = null;

    #[Groups(['full'])]
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLastId(): ?string
    {
        return $this->lastId;
    }

    public function setLastId(?string $lastId): static
    {
        $this->lastId = $lastId;

        return $this;
    }

    public function getLastCount(): ?int
    {
        return $this->lastCount;
    }

    public function setLastCount(?int $lastCount): static
    {
        $this->lastCount = $lastCount;

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
