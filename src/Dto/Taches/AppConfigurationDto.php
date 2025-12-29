<?php
namespace App\Dto\Taches;

use Symfony\Component\Validator\Constraints as Assert;

class AppConfigurationDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotNull]
        public ?string $value,

        #[Assert\NotNull]
        public ?bool $active,
    ) {
    }
}