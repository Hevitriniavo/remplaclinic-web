<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\RegionDto;
use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Region>
 */
class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['r.id', 'r.name'], 'r.id');
        
        $qb = $this->createQueryBuilder('r')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where('r.name LIKE :value')
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function save(RegionDto $specialityDto) {
        $em = $this->getEntityManager();

        $speciality = new Region();
        
        $speciality->setName($specialityDto->name);

        $em->persist($speciality);
        $em->flush();

        return $speciality;
    }

    public function update(Region $speciality, RegionDto $specialityDto) {
        $em = $this->getEntityManager();

        $speciality->setName($specialityDto->name);
        $em->flush();

        return $speciality;
    }

    public function remove(int $id) {
        $em = $this->getEntityManager();

        $speciality = $this->find($id);
        
        if (!is_null($speciality)) {
            $em->remove($speciality);
            $em->flush();
            return true;
        }

        return false;
    }
}
