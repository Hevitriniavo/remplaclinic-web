<?php
namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class EditRequestDto
{
    public function __construct(
        public ?int $applicant,

        public ?string $title,

        public ?int $status,

        public ?bool $showEndAt,

        public ?string $startedAt,

        public ?string $endAt,

        #[Assert\GreaterThan(value: 0)]
        public ?int $region,

        #[Assert\GreaterThan(value: 0)]
        public ?int $speciality,

        public ?array $subSpecialities,

        public ?int $positionCount,

        #[Assert\GreaterThanOrEqual(value: 0)]
        #[Assert\LessThanOrEqual(value: 2)]
        public ?int $accomodationIncluded,

        #[Assert\GreaterThanOrEqual(value: 0)]
        #[Assert\LessThanOrEqual(value: 2)]
        public ?int $transportCostRefunded,
        
        public ?string $remuneration,

        public ?string $retrocession,

        public ?string $replacementType,

        public ?array $raison,

        public ?string $raisonValue,

        public ?string $comment,
        
    ) {
    }
}