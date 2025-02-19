<?php

namespace App\Service\User;

use App\Common\DateUtil;
use App\Dto\User\ClinicDto;
use App\Dto\User\ReplacementDto;
use App\Dto\User\UserFilesDto;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\UserEstablishment;
use App\Entity\UserSubscription;
use App\Repository\RegionRepository;
use App\Repository\SpecialityRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class Registration
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserRoleRepository $userRoleRepository,
        private SpecialityRepository $specialityRepository,
        private RegionRepository $regionRepository,
        private FileUploader $fileUploader,
    ) {}

    public function register(ReplacementDto $replacementDto, UserFilesDto $files)
    {
        $user = new User();
        $userAddress = new UserAddress();
        $userAddress
            ->setCountry('FR')
            ->setThoroughfare($replacementDto->thoroughfare)
            ->setPremise($replacementDto->premise)
            ->setPostalCode($replacementDto->postalCode)
            ->setLocality($replacementDto->locality)
        ;

        $user
            ->setOrdinaryNumber($replacementDto->ordinaryNumber)
            ->setCivility($replacementDto->civility)
            ->setSurname($replacementDto->surname)
            ->setName($replacementDto->name)
            ->setYearOfBirth($replacementDto->yearOfBirth)
            ->setNationality($replacementDto->nationality)
            ->setEmail($replacementDto->email)
            ->setTelephone($replacementDto->telephone)
            ->setTelephone2($replacementDto->telephone2)
            ->setPassword($replacementDto->password)
            ->setStatus($replacementDto->status)
            ->setYearOfAlternance($replacementDto->yearOfResidency)
            ->setCurrentSpeciality($replacementDto->currentSpeciality)
            ->setComment($replacementDto->comment)
            ->setUserComment($replacementDto->userComment)
            ->setCv($this->fileUploader->upload($files->cv))
            ->setDiplom($this->fileUploader->upload($files->diplom))
            ->setLicence($this->fileUploader->upload($files->licence))
            ->setAddress($userAddress)
            ->setCreateAt(new DateTime())
        ;

        if (is_array($replacementDto->roles)) {
            foreach ($replacementDto->roles as $roleId) {
                $role = $this->userRoleRepository->find($roleId);
                if (is_null($role)) {
                    throw new EntityNotFoundException('No role found for ' . $roleId);
                }
                $user->addRole($role);
            }
        }

        if (!empty($replacementDto->speciality)) {
            $speciality = $this->specialityRepository->find($replacementDto->speciality);
            if (is_null($speciality)) {
                throw new EntityNotFoundException('No speciality found for ' . $replacementDto->speciality);
            }
            $user->setSpeciality($speciality);
        }

        if (is_array($replacementDto->subSpecialities)) {
            foreach ($replacementDto->subSpecialities as $specialityId) {
                $speciality = $this->specialityRepository->find($specialityId);
                if (is_null($speciality)) {
                    throw new EntityNotFoundException('No speciality found for ' . $specialityId);
                }
                $user->addSubSpeciality($speciality);
            }
        }

        if (is_array($replacementDto->mobility)) {
            foreach ($replacementDto->mobility as $mobilityId) {
                $region = $this->regionRepository->find($mobilityId);
                if (is_null($region)) {
                    throw new EntityNotFoundException('No region found for ' . $mobilityId);
                }
                $user->addMobility($region);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function registerClinic(ClinicDto $clinicDto)
    {
        $user = new User();
        $userAddress = new UserAddress();
        $userEstablishment = new UserEstablishment();
        $userSubscription = new UserSubscription();
        
        $userAddress
            ->setCountry('FR')
            ->setThoroughfare($clinicDto->thoroughfare)
            ->setPremise($clinicDto->premise)
            ->setPostalCode($clinicDto->postalCode)
            ->setLocality($clinicDto->locality)
        ;

        $userEstablishment
            ->setServiceName($clinicDto->serviceName)
            ->setChiefServiceName($clinicDto->chiefServiceName)
            ->setName($clinicDto->establishmentName)
            ->setBedsCount($clinicDto->bedsCount)
            ->setSiteWeb($clinicDto->siteWeb)
        ;

        $userSubscription
            ->setEndAt(DateUtil::parseDate('d/m/Y', $clinicDto->subscriptionEndAt))
            ->setStatus($clinicDto->subscriptionStatus)
            ->setEndNotification($clinicDto->subscriptionEndNotification)
            ->setInstallationCount($clinicDto->installationCount)
        ;

        $user
            ->setPosition($clinicDto->position)
            ->setCivility($clinicDto->civility)
            ->setSurname($clinicDto->surname)
            ->setName($clinicDto->name)
            ->setEmail($clinicDto->email)
            ->setTelephone($clinicDto->telephone)
            ->setTelephone2($clinicDto->telephone2)
            ->setPassword($clinicDto->password)
            ->setStatus($clinicDto->status)
            ->setFax($clinicDto->fax)
            ->setComment($clinicDto->comment)
            ->setAddress($userAddress)
            ->setEstablishment($userEstablishment)
            ->setSubscription($userSubscription)
            ->setCreateAt(new DateTime())
        ;

        if (is_array($clinicDto->roles)) {
            foreach ($clinicDto->roles as $roleId) {
                $role = $this->userRoleRepository->find($roleId);
                if (is_null($role)) {
                    throw new EntityNotFoundException('No role found for ' . $roleId);
                }
                $user->addRole($role);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
