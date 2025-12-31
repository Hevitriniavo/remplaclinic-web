<?php
namespace App\Message\Request;

use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class RequestMessageHandler
{
    public function __construct(
        private readonly RequestRepository $requestRepository,
        private readonly UserRepository $userRepository,
        private readonly RequestMessageMailBuilderInterface $mailBuilder,
        private readonly RequestMessageMailSenderInterface $mailSender,
    )
    {}

    public function __invoke(RequestMessage $message)
    {
        // @TODO: check fault tolerance for this handler because it should not:
        // - resend successfull user if faild
        // - ignore the next user
        $request = $this->requestRepository->find($message->getRequestId());
        if (is_null($request)) {
            return;
        }

        $eventName = $message->getEventName();
        $users = $message->getUsers(); // do not load for memory reason

        foreach($users as $userId) {
            $user = $this->userRepository->find($userId);
            
            // send email if not null or else continue in order to send the next email
            if (!is_null($user)) {
                $this->mailSender->send($this->mailBuilder->build($eventName, $request, $user));
            }
        }
    }
}