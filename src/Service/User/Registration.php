<?php

namespace App\Service\User;

use App\Common\DateUtil;
use App\Dto\User\ClinicDto;
use App\Dto\User\DirectorDto;
use App\Dto\User\DoctorDto;
use App\Dto\User\ReplacementDto;
use App\Dto\User\UserFilesDto;
use App\Entity\EmailEvents;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\UserEstablishment;
use App\Entity\UserSubscription;
use App\Exceptions\ApiException;
use App\Repository\RequestRepository;
use App\Security\SecurityUser;
use App\Service\FileUploader;
use App\Service\Mail\MailService;
use App\Service\Mail\RequestMailBuilder;
use App\Service\Taches\AppConfigurationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Registration
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService $userService,
        private readonly RoleService $roleService,
        private readonly SpecialityService $specialityService,
        private readonly RegionService $regionService,
        private readonly FileUploader $fileUploader,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AppConfigurationService $appConfiguration,
        private readonly MailService $mailService,
        private readonly RequestMailBuilder $mailBuilder,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    public function register(ReplacementDto $replacementDto, UserFilesDto $files): ?User
    {
        if (!$this->checkEmail($replacementDto->email)) {
            return null;
        }

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
            ->setPassword($this->hashPassword($replacementDto->password))
            ->setTelephone($replacementDto->telephone)
            ->setTelephone2($replacementDto->telephone2)
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
        } else {
            throw ApiException::make('Un remplaçant doit avoir une spécialité', 'REGISTRATION_SPECIALITY');
        }

        foreach ($this->specialityService->getSpecialities($replacementDto->subSpecialities) as $speciality) {
            $user->addSubSpeciality($speciality);
        }

        if (is_array($replacementDto->mobility)) {
            foreach ($this->regionService->getRegions($replacementDto->mobility) as $region) {
                $user->addMobility($region);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // send request notification
        $this->sendRequestInProgress($user, $replacementDto->speciality, $replacementDto->mobility);

        // send notification
        $this->sendInfosEmail($user, $replacementDto->password);
        $this->notifyAdmin($user, $replacementDto->password);

        return $user;
    }

    public function registerClinic(ClinicDto $clinicDto): ?User
    {
        if (!$this->checkEmail($clinicDto->email)) {
            return null;
        }

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
            ->setPassword($this->hashPassword($clinicDto->password))
            ->setTelephone($clinicDto->telephone)
            ->setTelephone2($clinicDto->telephone2)
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

        // send notification
        $this->sendInfosEmail($user, $clinicDto->password);
        $this->notifyAdmin($user, $clinicDto->password);

        return $user;
    }

    public function registerDoctor(DoctorDto $doctorDto): ?User
    {
        if (!$this->checkEmail($doctorDto->email)) {
            return null;
        }

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
            ->setPassword($this->hashPassword($doctorDto->password))
            ->setTelephone($doctorDto->telephone)
            ->setTelephone2($doctorDto->telephone2)
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

        // send notification
        $this->sendInfosEmail($user, $doctorDto->password);
        $this->notifyAdmin($user, $doctorDto->password);

        return $user;
    }

    public function registerDirector(DirectorDto $directorDto): ?User
    {
        if (!$this->checkEmail($directorDto->email)) {
            return null;
        }

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
            ->setPassword($this->hashPassword($directorDto->password))
            ->setTelephone($directorDto->telephone)
            ->setTelephone2($directorDto->telephone2)
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

        foreach ($this->userService->getUsers($directorDto->cliniques) as $clinic) {
            $user->addClinic($clinic);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // send notification
        $this->sendInfosEmail($user, $directorDto->password);
        $this->notifyAdmin($user, $directorDto->password);

        return $user;
    }

    private function checkEmail(string $email): bool
    {
        $existingUser = $this->entityManager->getRepository(User::class)->count([
            'email' => $email,
        ]);

        if ($existingUser > 0) {
            throw ApiException::make('Votre email est deja utilise dans une autre profile !', 'REGISTRATION_EMAIL_EXIST', 409);
        }

        return true;
    }

    private function hashPassword(string $rawPassword): string
    {
        $securityUser = new SecurityUser(new User());
        return $this->passwordHasher->hashPassword($securityUser, $rawPassword);
    }

    private function sendInfosEmail(User $user, string $rawPassword)
    {
        $mailLog = $this->mailBuilder
            ->build(EmailEvents::USER_INSCRIPTION, null, $user, [
                'raw_password' => $rawPassword,
            ]);
        $this->mailService->send($mailLog);
    }

    private function notifyAdmin(User $user, string $rawPassword)
    {
        $details = [
            User::ROLE_REPLACEMENT_ID => 'app_admin_replacement_show',
            User::ROLE_CLINIC_ID => 'app_admin_clinic_show',
            User::ROLE_DOCTOR_ID => 'app_admin_doctor_show',
            User::ROLE_DIRECTOR_ID => 'app_admin_director_show',
        ];

        $detailUrl = '#';
        if (array_key_exists($user->getRole()->getId(), $details)) {
            $detailUrl = $this->urlGenerator->generate($details[$user->getRole()->getId()], ['id' => $user->getId()]);
        }

        $mailLog = $this->mailBuilder
            ->build(EmailEvents::USER_INSCRIPTION_NOTIFICATION, null, $user, [
                'raw_password' => $rawPassword,
                'detail_url' => $detailUrl,
                'target_email' => $this->appConfiguration->getValue('USER_INSCRIPTION_TARGET_EMAIL', false, true)
            ]);
        $this->mailService->send($mailLog);
    }

    /**
     * @return Request[]
     */
    private function getRequestForUser(?int $speciality, ?array $regions): array
    {
        $params = [];
        if (!empty($speciality)) {
            $params['speciality'] = $speciality;
        }

        if (!empty($regions)) {
            $params['regions'] = $regions;
        }

        /**
         * @var RequestRepository
         */
        $repository = $this->entityManager->getRepository(Request::class);

        return $repository->findAllBy($params);
    }

    /**
     * @param User $user
     * @param int|null $speciality
     * @param int[]|null $mobility
     */
    private function sendRequestInProgress(User $user, ?int $speciality, ?array $mobility)
    {
        $requests = $this->getRequestForUser($speciality, $mobility);

        if (empty($requests)) {
            return;
        }

        $replacements = [];
        $installations = [];

        foreach($requests as $request) {
            $requestResponse = new RequestResponse();
            $requestResponse
                ->setRequest($request)
                ->setUser($user)
                ->setStatus(RequestResponse::EN_COURS)
            ;
            $this->entityManager->persist($requestResponse);

            if ($request->getStatus() == Request::IN_PROGRESS) {
                if ($request->getRequestType() === RequestType::REPLACEMENT) {
                    $replacements[] = $request;
                } else {
                    $installations[] = $request;
                }
            }
        }

        if (!empty($replacements)) {
            $mailLog = $this->mailBuilder
                ->build(EmailEvents::USER_CREATION_REQUEST_REPLACEMENT, null, $user, [
                    'requests' => $replacements,
                ]);
            $this->mailService->send($mailLog);
        }

        if (!empty($installations)) {
            $mailLog = $this->mailBuilder
                ->build(EmailEvents::USER_CREATION_REQUEST_INSTALLATION, null, $user, [
                    'requests' => $installations,
                ]);
            $this->mailService->send($mailLog);
        }
    }
}
