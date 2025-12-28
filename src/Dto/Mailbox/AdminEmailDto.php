<?php
namespace App\Dto\Mailbox;

use Symfony\Component\Validator\Constraints as Assert;

class AdminEmailDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotNull]
        public ?string $email,

        #[Assert\NotNull]
        public ?array $events,

        #[Assert\NotNull]
        public ?bool $status,
    ) {
    }
}