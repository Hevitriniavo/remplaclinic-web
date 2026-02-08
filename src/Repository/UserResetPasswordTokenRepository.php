<?php

namespace App\Repository;

use App\Entity\UserResetPasswordToken;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserResetPasswordToken>
 */
class UserResetPasswordTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserResetPasswordToken::class);
    }

    public function findOneByEmailAndCreatedAtGreaterThan(string $email, DateTime $expiredAt): ?UserResetPasswordToken
    {
        return $this->createQueryBuilder('rs')
            ->where('rs.email = :email')
            ->setParameter('email', $email)
            ->andWhere('rs.createdAt > :created_at')
            ->setParameter('created_at', $expiredAt->format('Y-m-d H:i'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByCodeAndCreatedAtGreaterThan(string $code, DateTime $expiredAt): ?UserResetPasswordToken
    {
        return $this->createQueryBuilder('rs')
            ->where('rs.code = :code')
            ->setParameter('code', $code)
            ->andWhere('rs.createdAt > :created_at')
            ->setParameter('created_at', $expiredAt->format('Y-m-d H:i'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
