<?php
namespace App\Dto\ResetPassword;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordEmailDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\NotNull]
        #[Assert\Length(max: 255)]
        public string $email
    ) {}
}