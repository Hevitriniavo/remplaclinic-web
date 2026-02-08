<?php
namespace App\Service\User;

use App\Entity\EmailEvents;
use App\Entity\ResetPasswordStatus;
use App\Entity\User;
use App\Entity\UserResetPasswordToken;
use App\Exceptions\ApiException;
use App\Security\SecurityUser;
use App\Service\Mail\MailService;
use App\Service\Mail\RequestMailBuilder;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ResetPasswordService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailService $mailService,
        private readonly RequestMailBuilder $mailBuilder,
    ) {}

    public function genererateCode(string $email): UserResetPasswordToken
    {
        $userRepository = $this->em->getRepository(User::class);
        $resetPasswordRepository = $this->em->getRepository(UserResetPasswordToken::class);
        
        $user = $userRepository->findByEmail($email);
        if (is_null($user)) {
            throw ApiException::make("Aucun compte utilisateur correspond a votre adresse email !", 'RESET_PASSWORD_NO_USER', 400);
        }

        $nowPlusFiveMinutes = new DateTime('-5 minutes');
        $activeToken = $resetPasswordRepository->findOneByEmailAndCreatedAtGreaterThan($email, $nowPlusFiveMinutes);
        if (!is_null($activeToken)) {
            throw ApiException::make("Vous avez deja un demande en cours. Veuillez attendre au moins 5 minutes avant d'effectuer une nouvelle demande.", 'RESET_PASSWORD_INTERVAL', 400);
        }

        $token = $this->updateAndCreateToken($user);

        $this->sendTokenEmail($user, $token);

        return $token;
    }

    public function checkCode(string $code): ?UserResetPasswordToken
    {
        $resetPasswordRepository = $this->em->getRepository(UserResetPasswordToken::class);

        $nowPlusFiveMinutes = new DateTime('-15 minutes');
        $activeToken = $resetPasswordRepository->findOneByCodeAndCreatedAtGreaterThan($code, $nowPlusFiveMinutes);
        if (is_null($activeToken)) {
            throw ApiException::make("Le code est expire. Veuillez demander un nouveau code ou contacter l'administrateur.", 'RESET_PASSWORD_EXPIRED_CODE', 400);
        }

        return $activeToken;
    }

    public function updatePassword(string $code, string $newPassword)
    {
        $token = $this->checkCode($code);
        $user = $token->getUser();

        $securityUser = new SecurityUser($user);

        $user->setPassword($this->passwordHasher->hashPassword($securityUser, $newPassword));
        $token->setStatus(ResetPasswordStatus::USED);

        $this->em->flush();
    }

    private function updateAndCreateToken(User $user): ?UserResetPasswordToken
    {
        $this->em->createQueryBuilder()
            ->update(UserResetPasswordToken::class, 'rs')
            ->where('rs.email = :email')
            ->setParameter('email', $user->getEmail())
            ->andWhere('rs.status = :status')
            ->setParameter('status', ResetPasswordStatus::CREATED)
            ->set('rs.status', ':new_status')
            ->setParameter('new_status', ResetPasswordStatus::EXPIRED)
            ->getQuery()
            ->execute();
        $this->em->flush();

        $newToken = (new UserResetPasswordToken)
            ->setCode(md5(sprintf('%d:%s', $user->getId(), bin2hex(random_bytes(8)))))
            ->setCreatedAt(new DateTimeImmutable())
            ->setExpiredAt(new DateTimeImmutable('+15 minutes'))
            ->setStatus(ResetPasswordStatus::CREATED)
            ->setEmail($user->getEmail())
            ->setUser($user);
        
        $this->em->persist($newToken);
        $this->em->flush();

        return $newToken;
    }

    private function sendTokenEmail(User $user, UserResetPasswordToken $token)
    {
        $mail = $this->mailBuilder->build(EmailEvents::USER_RESET_PASSWORD, null, $user, [
            'token' => $token,
        ]);

        $this->mailService->send($mail);
    }
}