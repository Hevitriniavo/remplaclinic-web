<?php
namespace App\Service\Request;

use App\Entity\EmailEvents;
use App\Entity\Request;
use App\Entity\RequestHistory;
use App\Exceptions\ApiException;
use App\Message\Request\RequestMessageDispatcher;
use App\Repository\RequestResponseRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class RenvoyerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestResponseRepository $requestResponseRepository,
        private readonly RequestMessageDispatcher $messageDispatcher,
    )
    {}

    public function execute(int $id): Request
    {
        // step 1: update sent date
        $request = $this->updateSentDate($id);

        // step 2: dispatch the message in order to run send emails in background process
        $this->dispatchSendEmailMessage($request, $this->requestResponseRepository->findAllUserIdsFor($request->getId()));

        return $request;
    }

    private function updateSentDate(int $id): Request
    {
        // on update statut et request history
        /**
         * @var Request
         */
        $request = $this->em->find(Request::class, $id);
        if (is_null($request)){
            throw new Exception('No request found for #' . $id);
        }

        // si statut n'est pas 'A valider'
        if ($request->getStatus() !== Request::IN_PROGRESS) {
            throw ApiException::make("Renvoi impossible: la demande n'est pas en cours.", 'REQUEST_RENVOIE_STATUS');
        }

        $requestHistory = (new RequestHistory())->setSentAt(new DateTimeImmutable());
        $request->setLastSentAt(new DateTimeImmutable())
            ->addSentDate($requestHistory);
        
        $this->em->persist($requestHistory);
        $this->em->flush();

        return $request;
    }

    private function dispatchSendEmailMessage(Request $request, array $usersId)
    {
        $this->messageDispatcher->dispatchSendEmailMessage(
            EmailEvents::REQUEST_RENVOIE,
            $request,
            $usersId
        );
    }
}