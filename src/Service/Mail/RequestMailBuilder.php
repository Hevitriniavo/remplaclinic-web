<?php
namespace App\Service\Mail;

use App\Entity\EmailEvents;
use App\Entity\Request;
use App\Entity\User;
use App\Entity\MailLog;
use App\Message\Request\RequestMessageMailBuilderInterface;
use App\Repository\AdminEmailRepository;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use App\Service\Mail\Contact\ContactNotificationAdminEmail;
use App\Service\Mail\RequestEmail\ArchiverRequestEmail;
use App\Service\Mail\RequestEmail\ValiderRequestEmail;
use App\Service\Mail\RequestEmail\RenvoyerRequestEmail;
use App\Service\Mail\RequestEmail\RelancerRequestEmail;
use App\Service\Mail\RequestEmail\RequestNotificationAdminEmail;
use App\Service\Mail\UserEmail\InscriptionNotificationAdminEmail;
use App\Service\Mail\UserRequestEmail\InscriptionRequestInstallationEmail;
use App\Service\Mail\UserRequestEmail\InscriptionRequestReplacementEmail;
use App\Service\Mail\UserEmail\InscriptionUserInfosEmail;
use App\Service\Mail\UserEmail\ResetPasswordEmail;
use App\Service\Mail\UserEmail\AbonnementExpirationAdminEmail;
use App\Service\Mail\UserRequestEmail\RequestResponseCoordonneeEmail;
use App\Service\Mail\UserRequestEmail\RequestResponseNotificationAdminEmail;
use App\Service\Mail\UserRequestEmail\RequestResponseOwnerNotificationEmail;
use Exception;
use Twig\Environment;

class RequestMailBuilder implements RequestMessageMailBuilderInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly AdminEmailRepository $adminEmailRepository,
        private readonly RequestRepository $requestRepository,
        private readonly UserRepository $userRepository,
    )
    {}

    /**
     * {@inheritdoc}
     */
    public function build(string $eventName, ?Request $request, ?User $user, array $options = []): MailLog
    {
        $emailBuilderMap = [
            EmailEvents::REQUEST_VALIDATION => ValiderRequestEmail::class,
            EmailEvents::REQUEST_RENVOIE => RenvoyerRequestEmail::class,
            EmailEvents::REQUEST_RELANCE => RelancerRequestEmail::class,
            EmailEvents::REQUEST_ARCHIVAGE => ArchiverRequestEmail::class,
            EmailEvents::USER_INSCRIPTION => InscriptionUserInfosEmail::class,
            EmailEvents::USER_INSCRIPTION_NOTIFICATION => InscriptionNotificationAdminEmail::class,
            EmailEvents::USER_CREATION_REQUEST_REPLACEMENT => InscriptionRequestReplacementEmail::class,
            EmailEvents::USER_CREATION_REQUEST_INSTALLATION => InscriptionRequestInstallationEmail::class,
            EmailEvents::USER_RESET_PASSWORD => ResetPasswordEmail::class,
            EmailEvents::USER_ABONNEMENT_EXPIRATION => AbonnementExpirationAdminEmail::class,
            EmailEvents::REQUEST_CREATION => RequestNotificationAdminEmail::class,
            EmailEvents::REQUEST_REPONSE_DEMANDEUR => RequestResponseOwnerNotificationEmail::class,
            EmailEvents::REQUEST_REPONSE_COORDONNEE => RequestResponseCoordonneeEmail::class,
            EmailEvents::REQUEST_REPONSE_ADMIN => RequestResponseNotificationAdminEmail::class,
            EmailEvents::CONTACT_CREATION => ContactNotificationAdminEmail::class,
        ];

        if (!array_key_exists($eventName, $emailBuilderMap)) {
            throw new Exception('No email builder found for {' . $eventName . '}');
        }

        $builderClass = $emailBuilderMap[$eventName];

        $mailLog = (new $builderClass(
            $this->twig,
            $request,
            $user,
            $options
        ))->getEmail();

        $ccAndBcc = $this->adminEmailRepository->findAllCcAndBcc($eventName);

        $mailLog
            ->setEvent($eventName)
            ->setBcc(implode(',', $ccAndBcc['bcc']))
            ->setCc(implode(',', $ccAndBcc['cc']));

        return $mailLog;
    }

    public function buildFromUserAndRequet(string $eventName, ?int $requestId, ?int $userId, array $options = []): MailLog
    {
        $request = null;
        if (!is_null($requestId)) {
            $request = $this->requestRepository->find($requestId);
            
            if (is_null($request)) {
                throw new Exception('Can not build email for not found request');
            }
        }

        $user = null;
        if (!is_null($userId)) {
            $user = $this->userRepository->find($userId);
            
            if (is_null($user)) {
                throw new Exception('Can not build email for not found user');
            }
        }

        return $this->build($eventName, $request, $user, $options);
    }
}