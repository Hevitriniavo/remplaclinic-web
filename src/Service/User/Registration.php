<?php

namespace App\Service\User;

use App\Common\DateUtil;
use App\Dto\User\ClinicDto;
use App\Dto\User\DirectorDto;
use App\Dto\User\DoctorDto;
use App\Dto\User\ReplacementDto;
use App\Dto\User\UserFilesDto;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\UserEstablishment;
use App\Entity\UserSubscription;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class Registration
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private RoleService $roleService,
        private SpecialityService $specialityService,
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

        foreach ($this->roleService->getRoles($replacementDto->roles) as $role) {
            $user->addRole($role);
        }

        if (!empty($replacementDto->speciality)) {
            $specialities = $this->specialityService->getSpecialities([$replacementDto->speciality]);
            $user->setSpeciality($specialities[0]);
        }

        foreach ($this->specialityService->getSpecialities($replacementDto->subSpecialities) as $speciality) {
            $user->addSubSpeciality($speciality);
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

        foreach ($this->roleService->getRoles($clinicDto->roles) as $role) {
            $user->addRole($role);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function registerDoctor(DoctorDto $doctorDto)
    {
        $user = new User();
        $userAddress = new UserAddress();
        $userEstablishment = new UserEstablishment();
        $userSubscription = new UserSubscription();
        
        $userAddress
            ->setCountry('FR')
            ->setThoroughfare($doctorDto->thoroughfare)
            ->setPremise($doctorDto->premise)
            ->setPostalCode($doctorDto->postalCode)
            ->setLocality($doctorDto->locality)
        ;

        $userEstablishment
            ->setConsultationCount($doctorDto->consultationCount)
            ->setPer($doctorDto->per)
            ->setSiteWeb($doctorDto->siteWeb)
        ;

        $userSubscription
            ->setEndAt(DateUtil::parseDate('d/m/Y', $doctorDto->subscriptionEndAt))
            ->setStatus($doctorDto->subscriptionStatus)
            ->setEndNotification($doctorDto->subscriptionEndNotification)
            ->setInstallationCount($doctorDto->installationCount)
        ;

        $user
            ->setOrdinaryNumber($doctorDto->ordinaryNumber)
            ->setCivility($doctorDto->civility)
            ->setSurname($doctorDto->surname)
            ->setName($doctorDto->name)
            ->setEmail($doctorDto->email)
            ->setTelephone($doctorDto->telephone)
            ->setTelephone2($doctorDto->telephone2)
            ->setPassword($doctorDto->password)
            ->setStatus($doctorDto->status)
            ->setFax($doctorDto->fax)
            ->setComment($doctorDto->comment)
            ->setAddress($userAddress)
            ->setEstablishment($userEstablishment)
            ->setSubscription($userSubscription)
            ->setCreateAt(new DateTime())
        ;

        foreach ($this->roleService->getRoles($doctorDto->roles) as $role) {
            $user->addRole($role);
        }

        if (!empty($doctorDto->speciality)) {
            $specialities = $this->specialityService->getSpecialities([$doctorDto->speciality]);
            $user->setSpeciality($specialities[0]);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function registerDirector(DirectorDto $directorDto)
    {
        $user = new User();
        $userAddress = new UserAddress();
        $userSubscription = new UserSubscription();
        
        $userAddress
            ->setCountry('FR')
            ->setThoroughfare($directorDto->thoroughfare)
            ->setPremise($directorDto->premise)
            ->setPostalCode($directorDto->postalCode)
            ->setLocality($directorDto->locality)
        ;

        $userSubscription
            ->setEndAt(DateUtil::parseDate('d/m/Y', $directorDto->subscriptionEndAt))
            ->setStatus($directorDto->subscriptionStatus)
            ->setEndNotification($directorDto->subscriptionEndNotification)
        ;

        $user
            ->setPosition($directorDto->position)
            ->setCivility($directorDto->civility)
            ->setSurname($directorDto->surname)
            ->setName($directorDto->name)
            ->setEmail($directorDto->email)
            ->setTelephone($directorDto->telephone)
            ->setTelephone2($directorDto->telephone2)
            ->setPassword($directorDto->password)
            ->setStatus($directorDto->status)
            ->setFax($directorDto->fax)
            ->setOrganism($directorDto->organism)
            ->setComment($directorDto->comment)
            ->setAddress($userAddress)
            ->setSubscription($userSubscription)
            ->setCreateAt(new DateTime())
        ;

        foreach ($this->roleService->getRoles($directorDto->roles) as $role) {
            $user->addRole($role);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
