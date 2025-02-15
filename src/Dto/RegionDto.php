<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegionDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
    ) {
    }
}