<?php
namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class NewReplacementDto
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
        #[Assert\GreaterThanOrEqual(value: 1, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 10, groups: ['create'])]
        public ?int $positionCount,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\GreaterThanOrEqual(value: 0, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 2, groups: ['create'])]
        public ?int $accomodationIncluded,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\GreaterThanOrEqual(value: 0, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 2, groups: ['create'])]
        public ?int $transportCostRefunded,

        public ?string $remuneration,

        public ?string $retrocession,

        public ?string $replacementType,

        public ?string $comment,
        
        public ?string $startedAt,

        public ?string $endAt,

        public ?array $subSpecialities,
    ) {
    }
}