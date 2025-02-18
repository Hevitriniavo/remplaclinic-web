<?php
namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class ReplacementDto
{
    public function __construct(
        #[Assert\NotBlank(groups: ['create'])]
        public ?string $civility,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $surname,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $name,

        public ?string $ordinaryNumber,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $telephone,

        public ?string $telephone2,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $thoroughfare,

        public ?string $premise,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $postalCode,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $locality,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\GreaterThan(value: 0, groups: ['create'])]
        public ?int $yearOfBirth,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $nationality,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $email,

        #[Assert\NotBlank(groups: ['create'])]
        public ?string $password,

        #[Assert\NotBlank(groups: ['create'])]
        #[Assert\GreaterThanOrEqual(value: 0, groups: ['create'])]
        #[Assert\LessThanOrEqual(value: 1, groups: ['create'])]
        public ?string $status,

        #[Assert\NotNull(groups: ['create'])]
        public ?array $roles,

        #[Assert\GreaterThan(value: 0, groups: ['create'])]
        public ?int $yearOfResidency,

        #[Assert\NotNull(groups: ['create'])]
        public ?string $currentSpeciality,
        
        public ?int $speciality,

        public ?array $subSpecialities,

        public ?array $mobility,

        public ?string $comment,

        public ?string $userComment
    ) {
    }
}