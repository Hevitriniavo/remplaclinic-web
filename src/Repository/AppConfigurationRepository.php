<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\AppConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<AppConfiguration>
 */
class AppConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppConfiguration::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['a.id', 'a.id', 'a.name', 'a.value', 'a.active'], 'a.id');
        
        $qb = $this->createQueryBuilder('a')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where($qb->expr()->orX(
                'a.name LIKE :value',
                'a.value LIKE :value'
            ))
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function save(AppConfiguration $appConfiguration): ?AppConfiguration
    {
        $this->checkName($appConfiguration);

        $em = $this->getEntityManager();

        $em->persist($appConfiguration);
        $em->flush();

        return $appConfiguration;
    }

    public function update(AppConfiguration $appConfiguration): ?AppConfiguration
    {
        $this->checkName($appConfiguration, $appConfiguration->getId());

        $em = $this->getEntityManager();

        $em->flush();

        return $appConfiguration;
    }

    private function checkName(AppConfiguration $appConfiguration, ?int $id = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.name = :name')
            ->setParameter('name', $appConfiguration->getName())
            ->setMaxResults(1);

        if (!empty($id)) {
            $qb->andWhere('a.id <> :id')
                ->setParameter('id', $id);
        }

        $exist = $qb->getQuery()
            ->getOneOrNullResult();

        if (!is_null($exist)) {
            throw new Exception('Il existe une configuration portant le meme nom que {' . $appConfiguration->getName() . '} dans la base de donnees.');
        }
    }
}
