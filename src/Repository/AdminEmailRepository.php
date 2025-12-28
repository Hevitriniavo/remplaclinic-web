<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\AdminEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminEmail>
 */
class AdminEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminEmail::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['a.id', 'a.name', 'a.email', 'a.status'], 'a.id');
        
        $qb = $this->createQueryBuilder('a')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where($qb->expr()->orX(
                'a.name LIKE :value',
                'a.email LIKE :value',
                'a.events LIKE :value',
            ))
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function save(AdminEmail $adminEmail): ?AdminEmail
    {
        $em = $this->getEntityManager();

        $em->persist($adminEmail);
        $em->flush();

        return $adminEmail;
    }

    public function update(AdminEmail $adminEmail): ?AdminEmail
    {
        $em = $this->getEntityManager();

        $em->flush();

        return $adminEmail;
    }

    public function remove(int $id): ?AdminEmail
    {
        $em = $this->getEntityManager();

        $adminEmail = $this->find($id);
        
        if (!is_null($adminEmail)) {
            $em->remove($adminEmail);
            $em->flush();
            return $adminEmail;
        }

        return null;
    }
}
