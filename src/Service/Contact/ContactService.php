<?php

namespace App\Service\Contact;

use App\Dto\ContactDto;
use App\Entity\Contact;
use App\Entity\ContactObject;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ContactService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function submitContact(ContactDto $contactDto, ?User $user): ?Contact
    {
        // step 1: store contact
        $contact = new Contact();
        $contact
            ->setContactType($contactDto->contact_type)
            ->setRemoteAddr($contactDto->remote_addr)
            ->setUserId($user ? $user->getId() : null)
            ->setName($contactDto->name)
            ->setSurname($contactDto->surname)
            ->setTelephone($contactDto->telephone)
            ->setEmail($contactDto->email)
            ->setMessage($contactDto->message)
            ->setObject(empty($contactDto->object) ? [] : array_map(fn($item) => ContactObject::tryFrom($item), $contactDto->object))
            ->setFonction($contactDto->fonction)
            ->setSubmittedAt(new DateTimeImmutable())
        ;

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        // step 2: notify admin
        // $this->notifyAdminNewRequest($contact);

        return $contact;
    }

    public function deleteContact(int $contactId): bool
    {
        $contact = $this->entityManager->find(Contact::class, $contactId);
        if (!is_null($contact)) {
            $this->entityManager->remove($contact);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function deleteMultipleContact(array $contactsId): bool
    {
        $contacts = $this->entityManager->getRepository(Contact::class)->findBy(['id' => $contactsId]);
        if (!empty($contacts)) {

            foreach($contacts as $contact) {
                $this->entityManager->remove($contact);
            }

            $this->entityManager->flush();

            return true;
        }
        return false;
    }
}
