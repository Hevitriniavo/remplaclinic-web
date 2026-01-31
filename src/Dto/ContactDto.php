<?php
namespace App\Dto;

use App\Entity\Contact;
use Symfony\Component\Validator\Constraints as Assert;

class ContactDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Choice(choices: [ Contact::CONTACT_DEFAULT, Contact::CONTACT_ASSISTANCE, Contact::CONTACT_OUVERTURE_COMPTE, Contact::CONTACT_INSTAL_CLINIC ])]
        public int $contact_type,

        #[Assert\NotBlank]
        #[Assert\NotNull]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\Length(max: 255)]
        public ?string $surname,

        #[Assert\NotBlank]
        #[Assert\NotNull]
        #[Assert\Length(max: 255)]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\NotNull]
        public string $message,

        #[Assert\Length(max: 255)]
        public ?string $telephone,

        #[Assert\Length(max: 255)]
        public ?string $fonction,

        public ?array $object,
        
        #[Assert\Length(max: 255)]
        public ?string $remote_addr,
    ) {
    }
}