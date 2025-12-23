<?php
namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class NewInstallationDto
{
    public function __construct(
        #[Assert\NotNull(groups: ['create'])]
        #[Assert\GreaterThan(value: 0, groups: ['create'])]
        public ?int $applicant,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\GreaterThan(value: 0, groups: ['create'])]
        public ?int $speciality,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\GreaterThan(value: 0, groups: ['create'])]
        public ?int $region,

        #[Assert\NotNull(groups: ['create'])]
        public ?array $raison,

        public ?string $raisonValue,

        public ?string $remuneration,

        public ?string $comment,
        
        public ?string $startedAt,
    ) {
    }
}