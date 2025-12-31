<?php
namespace App\Dto\Taches;

use Symfony\Component\Validator\Constraints as Assert;

class AppSchedulerDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public string $label,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public string $script,

        public ?array $options,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $time,
    ) {
    }
}