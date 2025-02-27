<?php

namespace App\Service\User;

use App\Common\DateUtil;
use App\Dto\User\ClinicDto;
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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class UserUpdate
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private RoleService $roleService,
        private SpecialityService $specialityService,
        private RegionRepository $regionRepository,
        private FileUploader $fileUploader,
    ) {}

    public function update(User $user, ReplacementDto $replacementDto, UserFilesDto $files)
    {
        $userAddress = $user->getAddress();
        if (empty($userAddress)) {
            $userAddress = (new UserAddress())
                ->setCountry('FR')
            ;
        }

        $updated1 = $this->updateAttribute($userAddress, 'setThoroughfare', $replacementDto->thoroughfare);
        $updated2 = $this->updateAttribute($userAddress, 'setPremise', $replacementDto->premise);
        $updated3 = $this->updateAttribute($userAddress, 'setPostalCode', $replacementDto->postalCode);
        $updated4 = $this->updateAttribute($userAddress, 'setLocality', $replacementDto->locality);
        
        if ($updated1 || $updated2 || $updated3 || $updated4) {
            $user->setAddress($userAddress);
        }
        
        $this->updateAttribute($user, 'setOrdinaryNumber', $replacementDto->ordinaryNumber);
        $this->updateAttribute($user, 'setCivility', $replacementDto->civility);
        $this->updateAttribute($user, 'setSurname', $replacementDto->surname);
        $this->updateAttribute($user, 'setName', $replacementDto->name);
        $this->updateAttribute($user, 'setYearOfBirth', $replacementDto->yearOfBirth);
        $this->updateAttribute($user, 'setNationality', $replacementDto->nationality);
        $this->updateAttribute($user, 'setEmail', $replacementDto->email);
        $this->updateAttribute($user, 'setTelephone', $replacementDto->telephone);
        $this->updateAttribute($user, 'setTelephone2', $replacementDto->telephone2);
        $this->updateAttribute($user, 'setStatus', $replacementDto->status);
        $this->updateAttribute($user, 'setYearOfAlternance', $replacementDto->yearOfResidency);
        $this->updateAttribute($user, 'setCurrentSpeciality', $replacementDto->currentSpeciality);
        $this->updateAttribute($user, 'setComment', $replacementDto->comment);
        $this->updateAttribute($user, 'setUserComment', $replacementDto->userComment);
        $this->updateAttribute($user, 'setCv', $this->fileUploader->upload($files->cv));
        $this->updateAttribute($user, 'setDiplom', $this->fileUploader->upload($files->diplom));
        $this->updateAttribute($user, 'setLicence', $this->fileUploader->upload($files->licence));

        if (is_array($replacementDto->roles)) {
            $user->clearRole();
            foreach ($this->roleService->getRoles($replacementDto->roles) as $role) {
                $user->addRole($role);
            }
        }

        if (!empty($doctorDto->speciality)) {
            $specialities = $this->specialityService->getSpecialities([$replacementDto->speciality]);
            $user->setSpeciality($specialities[0]);
        }

        if (is_array($replacementDto->subSpecialities)) {
            $user->clearSubSpeciality();
            foreach ($this->specialityService->getSpecialities($replacementDto->subSpecialities) as $speciality) {
                $user->addSubSpeciality($speciality);
            }
        }

        if (is_array($replacementDto->mobility)) {
            $user->clearMobility();
            foreach ($replacementDto->mobility as $mobilityId) {
                $region = $this->regionRepository->find($mobilityId);
                if (is_null($region)) {
                    throw new EntityNotFoundException('No region found for ' . $mobilityId);
                }
                $user->addMobility($region);
            }
        }

        $this->entityManager->flush();

        return $user;
    }

    public function updateClinic(User $user, ClinicDto $clinicDto)
    {
        $userAddress = $user->getAddress();
        $userSubscription = $user->getSubscription();
        $userEstablishment = $user->getEstablishment();

        if (empty($userAddress)) {
            $userAddress = (new UserAddress())
                ->setCountry('FR')
            ;
        }

        if (empty($userSubscription)) {
            $userSubscription = (new UserSubscription())
                ->setInstallationCount(0)
            ;
        }

        if (empty($userEstablishment)) {
            $userEstablishment = new UserEstablishment();
        }

        $updated1 = $this->updateAttribute($userAddress, 'setThoroughfare', $clinicDto->thoroughfare);
        $updated2 = $this->updateAttribute($userAddress, 'setPremise', $clinicDto->premise);
        $updated3 = $this->updateAttribute($userAddress, 'setPostalCode', $clinicDto->postalCode);
        $updated4 = $this->updateAttribute($userAddress, 'setLocality', $clinicDto->locality);
        
        if ($updated1 || $updated2 || $updated3 || $updated4) {
            $user->setAddress($userAddress);
        }

        $updated1 = $this->updateAttribute($userEstablishment, 'setServiceName', $clinicDto->serviceName);
        $updated2 = $this->updateAttribute($userEstablishment, 'setChiefServiceName', $clinicDto->chiefServiceName);
        $updated3 = $this->updateAttribute($userEstablishment, 'setName', $clinicDto->establishmentName);
        $updated4 = $this->updateAttribute($userEstablishment, 'setBedsCount', $clinicDto->bedsCount);
        $updated5 = $this->updateAttribute($userEstablishment, 'setSiteWeb', $clinicDto->siteWeb);
        
        if ($updated1 || $updated2 || $updated3 || $updated4 || $updated5) {
            $user->setEstablishment($userEstablishment);
        }

        $updated1 = $this->updateAttribute($userSubscription, 'setEndAt', DateUtil::parseDate('d/m/Y', $clinicDto->subscriptionEndAt, true));
        $updated2 = $this->updateAttribute($userSubscription, 'setStatus', $clinicDto->status);
        $updated3 = $this->updateAttribute($userSubscription, 'setEndNotification', $clinicDto->subscriptionEndNotification);
        $updated4 = $this->updateAttribute($userSubscription, 'setInstallationCount', $clinicDto->installationCount);
        
        if ($updated1 || $updated2 || $updated3 || $updated4) {
            $user->setSubscription($userSubscription);
        }

        $this->updateAttribute($user, 'setPosition', $clinicDto->position);
        $this->updateAttribute($user, 'setCivility', $clinicDto->civility);
        $this->updateAttribute($user, 'setSurname', $clinicDto->surname);
        $this->updateAttribute($user, 'setName', $clinicDto->name);
        $this->updateAttribute($user, 'setEmail', $clinicDto->email);
        $this->updateAttribute($user, 'setTelephone', $clinicDto->telephone);
        $this->updateAttribute($user, 'setTelephone2', $clinicDto->telephone2);
        $this->updateAttribute($user, 'setStatus', $clinicDto->status);
        $this->updateAttribute($user, 'setFax', $clinicDto->fax);
        $this->updateAttribute($user, 'setComment', $clinicDto->comment);

        if (is_array($clinicDto->roles)) {
            $user->clearRole();
            foreach ($this->roleService->getRoles($clinicDto->roles) as $role) {
                $user->addRole($role);
            }
        }

        $this->entityManager->flush();

        return $user;
    }

    public function updateDoctor(User $user, DoctorDto $doctorDto)
    {
        $userAddress = $user->getAddress();
        $userSubscription = $user->getSubscription();
        $userEstablishment = $user->getEstablishment();

        if (empty($userAddress)) {
            $userAddress = (new UserAddress())
                ->setCountry('FR')
            ;
        }

        if (empty($userSubscription)) {
            $userSubscription = (new UserSubscription())
                ->setInstallationCount(0)
            ;
        }

        if (empty($userEstablishment)) {
            $userEstablishment = new UserEstablishment();
        }

        $updated1 = $this->updateAttribute($userAddress, 'setThoroughfare', $doctorDto->thoroughfare);
        $updated2 = $this->updateAttribute($userAddress, 'setPremise', $doctorDto->premise);
        $updated3 = $this->updateAttribute($userAddress, 'setPostalCode', $doctorDto->postalCode);
        $updated4 = $this->updateAttribute($userAddress, 'setLocality', $doctorDto->locality);
        
        if ($updated1 || $updated2 || $updated3 || $updated4) {
            $user->setAddress($userAddress);
        }

        $updated1 = $this->updateAttribute($userEstablishment, 'setPer', $doctorDto->per);
        $updated2 = $this->updateAttribute($userEstablishment, 'setConsultationCount', $doctorDto->consultationCount);
        $updated3 = $this->updateAttribute($userEstablishment, 'setSiteWeb', $doctorDto->siteWeb);
        
        if ($updated1 || $updated2 || $updated3) {
            $user->setEstablishment($userEstablishment);
        }

        $updated1 = $this->updateAttribute($userSubscription, 'setEndAt', DateUtil::parseDate('d/m/Y', $doctorDto->subscriptionEndAt, true));
        $updated2 = $this->updateAttribute($userSubscription, 'setStatus', $doctorDto->status);
        $updated3 = $this->updateAttribute($userSubscription, 'setEndNotification', $doctorDto->subscriptionEndNotification);
        $updated4 = $this->updateAttribute($userSubscription, 'setInstallationCount', $doctorDto->installationCount);
        
        if ($updated1 || $updated2 || $updated3 || $updated4) {
            $user->setSubscription($userSubscription);
        }

        $this->updateAttribute($user, 'setOrdinaryNumber', $doctorDto->ordinaryNumber);
        $this->updateAttribute($user, 'setCivility', $doctorDto->civility);
        $this->updateAttribute($user, 'setSurname', $doctorDto->surname);
        $this->updateAttribute($user, 'setName', $doctorDto->name);
        $this->updateAttribute($user, 'setEmail', $doctorDto->email);
        $this->updateAttribute($user, 'setTelephone', $doctorDto->telephone);
        $this->updateAttribute($user, 'setTelephone2', $doctorDto->telephone2);
        $this->updateAttribute($user, 'setStatus', $doctorDto->status);
        $this->updateAttribute($user, 'setFax', $doctorDto->fax);
        $this->updateAttribute($user, 'setComment', $doctorDto->comment);

        if (is_array($doctorDto->roles)) {
            $user->clearRole();
            foreach ($this->roleService->getRoles($doctorDto->roles) as $role) {
                $user->addRole($role);
            }
        }

        if (!empty($doctorDto->speciality)) {
            $specialities = $this->specialityService->getSpecialities([$doctorDto->speciality]);
            $user->setSpeciality($specialities[0]);
        }

        $this->entityManager->flush();

        return $user;
    }

    private function updateAttribute($entity, $setter, $value)
    {
        if (!is_null($value)) {
            $entity->$setter($value);

            return true;
        }

        return false;
    }
}
