<?php
namespace App\Service\Request;

use App\Entity\EmailEvents;
use App\Entity\Request;
use App\Message\Request\RequestMessageMailBuilderInterface;
use App\Message\Request\RequestMessageMailSenderInterface;
use App\Repository\RequestRepository;
use App\Repository\RequestResponseRepository;
use Exception;

class RelancerService
{
    public function __construct(
        private readonly RequestRepository $requestRepository,
        private readonly RequestResponseRepository $requestResponseRepository,
        private readonly RequestMessageMailSenderInterface $mailSender,
        private readonly RequestMessageMailBuilderInterface $mailBuilder,
    )
    {}

    public function execute(int $id): Request
    {
        // step 0: get request
        $request = $this->getRequest($id);

        // step 1: get all accepted responses
        $users = $this->requestResponseRepository->findAllUserWhoAccept($id);

        // step 2: build the email
        $mailLog = $this->mailBuilder->build(EmailEvents::REQUEST_RELANCE, $request, null, [
            'users' => $users,
        ]);

        // step 4: send the email
        $this->mailSender->send($mailLog);

        return $request;
    }

    private function getRequest(int $id): Request
    {
        /**
         * @var Request
         */
        $request = $this->requestRepository->find($id);
        if (is_null($request)){
            throw new Exception('No request found for #' . $id);
        }

        return $request;
    }
}