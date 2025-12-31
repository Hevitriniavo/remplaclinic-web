<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\AppScheduler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppScheduler>
 */
class AppSchedulerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppScheduler::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['i.id', 'i.id', 'i.label', 'i.script', 'i.id', 'i.time', 'i.executedAt'], 'i.id');
        
        $qb = $this->createQueryBuilder('i')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where($qb->expr()->orX(
                'i.label LIKE :value',
                'i.script LIKE :value',
                'i.time LIKE :value',
                'i.executedAt LIKE :value',
            ))
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }
}
