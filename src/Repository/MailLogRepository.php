<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\MailLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MailLog>
 */
class MailLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailLog::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['m.id', 'm.id', 'm.event', 'm.sentAt', 'm.id', 'm.id', 'm.id', 'm.target'], 'm.id');
        
        $qb = $this->createQueryBuilder('m')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where($qb->expr()->orX(
                'm.event LIKE :value',
                'm.createdAt LIKE :value',
                'm.subject LIKE :value',
                'm.target LIKE :value',
                'm.cc LIKE :value',
                'm.bcc LIKE :value',
            ))
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }
}
