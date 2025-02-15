<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SpecialityDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        #[Assert\GreaterThan(0)]
        public ?int $specialityParent,
    ) {
    }
}