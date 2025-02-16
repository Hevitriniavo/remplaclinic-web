<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PartenaireLogoDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name
    ) {
    }
}