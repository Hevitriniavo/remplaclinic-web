<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class DoctorDto
{
    public function __construct(
        public ?string $ordinaryNumber,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $civility,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $surname,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $name,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $telephone,

        public ?string $telephone2,

        public ?string $fax,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $thoroughfare,

        public ?string $premise,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $postalCode,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $locality,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $email,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $password,

        #[Assert\NotBlank(groups: ['create'])]
        #[Assert\GreaterThanOrEqual(value: 0, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 1, groups: ['create'])]
        public ?int $status,

        #[Assert\NotNull(groups: ['create'])]
        public ?array $roles,

        public ?int $speciality,
        
        public ?string $per,

        public ?int $consultationCount,
        
        public ?string $siteWeb,
        
        public ?string $comment,
        
        #[Assert\GreaterThanOrEqual(value: 0, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 1, groups: ['create'])]
        public ?int $subscriptionStatus,
        
        public ?string $subscriptionEndAt,
        
        #[Assert\GreaterThanOrEqual(value: 0, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 1, groups: ['create'])]
        public ?int $subscriptionEndNotification,

        public ?int $installationCount,
    ) {
    }
}