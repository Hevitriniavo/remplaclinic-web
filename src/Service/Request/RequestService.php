<?php

namespace App\Service\Request;

use App\Common\DateUtil;
use App\Dto\Request\EditRequestDto;
use App\Dto\Request\NewInstallationDto;
use App\Dto\Request\NewReplacementDto;
use App\Entity\Request;
use App\Entity\RequestReason;
use App\Entity\RequestReplacementType;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Repository\RequestRepository;
use App\Service\User\RegionService;
use App\Service\User\SpecialityService;
use App\Service\User\UserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class RequestService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestRepository $requestRepository,
        private SpecialityService $specialityService,
        private RegionService $regionService,
        private UserService $userService,
    ) {}

    public function createReplacement(NewReplacementDto $replacementDto): ?Request
    {
        $request = new Request();
        $request->setApplicant($this->userService->getUser($replacementDto->applicant))
            ->setSpeciality($this->specialityService->getSpeciality($replacementDto->speciality))
            ->setRegion($this->regionService->getRegion($replacementDto->region))
            ->setPositionCount($replacementDto->positionCount)
            ->setAccomodationIncluded($replacementDto->accomodationIncluded)
            ->setTransportCostRefunded($replacementDto->transportCostRefunded)
            ->setRemuneration($replacementDto->remuneration)
            ->setRetrocession($replacementDto->retrocession)
            ->setComment($replacementDto->comment)
            ->setStartedAt(DateUtil::parseDate('d/m/Y', $replacementDto->startedAt, true))
            ->setEndAt(DateUtil::parseDate('d/m/Y', $replacementDto->endAt, true))
            ->setReplacementType(RequestReplacementType::tryFrom($replacementDto->replacementType))
            ->setRequestType(RequestType::REPLACEMENT)
            ->setCreatedAt(new DateTime())
            ->setTitle($this->getRequestTitle(RequestType::REPLACEMENT, $replacementDto->startedAt, $replacementDto->endAt))
            ->setShowEndAt(!is_null($replacementDto->endAt))
            ->setStatus(Request::CREATED)
        ;

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        return $request;
    }

    public function createInstallation(NewInstallationDto $installationDto): ?Request
    {
        $request = new Request();
        $request->setApplicant($this->userService->getUser($installationDto->applicant))
            ->setSpeciality($this->specialityService->getSpeciality($installationDto->speciality))
            ->setRegion($this->regionService->getRegion($installationDto->region))
            ->setRemuneration($installationDto->remuneration)
            ->setComment($installationDto->comment)
            ->setStartedAt(DateUtil::parseDate('d/m/Y', $installationDto->startedAt, true))
            ->setRequestType(RequestType::INSTALLATION)
            ->setCreatedAt(new DateTime())
            ->setTitle($this->getRequestTitle(RequestType::INSTALLATION, $installationDto->startedAt))
            ->setShowEndAt(false)
            ->setStatus(Request::CREATED)
        ;

        $this->populateReasons($request, $installationDto->raison, $installationDto->raisonValue);

        foreach($request->getReasons() as $reason) {
            $this->entityManager->persist($reason);
        }

        $this->entityManager->persist($request);
        $this->entityManager->flush();

        return $request;
    }

    public function updateReplacement(Request $request, EditRequestDto $requestDto): ?Request
    {
        if ($request->getRequestType() !== RequestType::REPLACEMENT) {
            return $request;
        }

        $request->setApplicant($this->userService->getUser($requestDto->applicant))
            ->setSpeciality($this->specialityService->getSpeciality($requestDto->speciality))
            ->setRegion($this->regionService->getRegion($requestDto->region))
            ->setPositionCount($requestDto->positionCount)
            ->setAccomodationIncluded($requestDto->accomodationIncluded)
            ->setTransportCostRefunded($requestDto->transportCostRefunded)
            ->setRemuneration($requestDto->remuneration)
            ->setRetrocession($requestDto->retrocession)
            ->setComment($requestDto->comment)
            ->setStartedAt(DateUtil::parseDate('d/m/Y', $requestDto->startedAt, true))
            ->setEndAt(DateUtil::parseDate('d/m/Y', $requestDto->endAt, true))
            ->setReplacementType(RequestReplacementType::tryFrom($requestDto->replacementType))
            ->setTitle($requestDto->title)
            ->setShowEndAt(!is_null($requestDto->endAt))
            ->setStatus($requestDto->status)
        ;

        if (!empty($requestDto->subSpecialities)) {
            $subSpecialities = $this->specialityService->getSpecialities($requestDto->subSpecialities);

            $request->clearSubSpeciality();

            foreach($subSpecialities as $speciality) {
                $request->addSubSpeciality($speciality);
            }
        }

        $this->entityManager->flush();

        return $request;
    }

    public function updateInstallation(Request $request, EditRequestDto $requestDto): ?Request
    {
        if ($request->getRequestType() !== RequestType::INSTALLATION) {
            return $request;
        }

        $request->setApplicant($this->userService->getUser($requestDto->applicant))
            ->setSpeciality($this->specialityService->getSpeciality($requestDto->speciality))
            ->setRegion($this->regionService->getRegion($requestDto->region))
            ->setRemuneration($requestDto->remuneration)
            ->setComment($requestDto->comment)
            ->setStartedAt(DateUtil::parseDate('d/m/Y', $requestDto->startedAt, true))
            ->setTitle($requestDto->title)
            ->setShowEndAt(!is_null($requestDto->endAt))
            ->setStatus($requestDto->status)
        ;

        $this->populateReasons($request, $requestDto->raison, $requestDto->raisonValue);

        foreach($request->getReasons() as $reason) {
            $this->entityManager->persist($reason);
        }

        if (!is_null($requestDto->endAt)) {
            $request->setEndAt(DateUtil::parseDate('d/m/Y', $requestDto->endAt, true));
        }

        $this->entityManager->flush();

        return $request;
    }

    public function deleteRequest(int $requestId): bool
    {
        $request = $this->requestRepository->find($requestId);
        if (!is_null($request)) {
            $this->entityManager->remove($request);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function deleteMultipleRequest(array $requestsId): bool
    {
        $requests = $this->requestRepository->findBy(['id' => $requestsId]);
        if (!empty($requests)) {

            foreach($requests as $request) {
                $this->entityManager->remove($request);
            }

            $this->entityManager->flush();

            return true;
        }
        return false;
    }

    public function initRequestResponse(Request $request, array $usersId): void
    {
        $users = $this->userService->getUsers($usersId);

        foreach($users as $user) {
            $reqResp = new RequestResponse();
            $reqResp
                ->setStatus(RequestResponse::EN_COURS)
                ->setRequest($request)
                ->setUser($user)
            ;

            $this->entityManager->persist($reqResp);
        }

        $this->entityManager->flush();
    }

    private function getRequestTitle(RequestType $type, $start, $end = null): string
    {
        $dates = [$start];
        if (!is_null($end)) {
            $dates[] = $end;
        }
        return ($type === RequestType::INSTALLATION ? "Proposition d'installation à partir du " : 'Demande de remplaçement du ') . join(' au ', $dates);
    }

    private function populateReasons(Request $request, ?array $reasons, ?string $raisonValue)
    {
        if (!is_null($reasons)) {
            $request->clearReasons();
            foreach ($reasons as $reason) {
                $r = new RequestReason();
                $r->setReason($reason);

                if (strtolower(trim($reason)) === RequestReason::OTHER) {
                    $r->setReasonValue($raisonValue);
                }

                $request->addReason($r);
            }
        }
    }
}
