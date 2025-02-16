<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class EvidenceDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,

        #[Assert\NotBlank]
        public string $body,

        #[Assert\NotBlank]
        public string $clinicName,

        #[Assert\NotBlank]
        public string $specialityName,
    ) {
    }
}