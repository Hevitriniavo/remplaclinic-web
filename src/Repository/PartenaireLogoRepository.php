<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\PartenaireLogo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartenaireLogo>
 */
class PartenaireLogoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartenaireLogo::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['p.id', 'p.name'], 'p.id');
        
        $qb = $this->createQueryBuilder('p')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where('p.name LIKE :value')
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function save(PartenaireLogo $partenaireLogo): ?PartenaireLogo
    {
        $em = $this->getEntityManager();

        $em->persist($partenaireLogo);
        $em->flush();

        return $partenaireLogo;
    }

    public function update(PartenaireLogo $partenaire): ?PartenaireLogo
    {
        $em = $this->getEntityManager();

        $em->flush();

        return $partenaire;
    }

    public function remove(int $id): ?PartenaireLogo
    {
        $em = $this->getEntityManager();

        $partenaire = $this->find($id);
        
        if (!is_null($partenaire)) {
            $em->remove($partenaire);
            $em->flush();
            return $partenaire;
        }

        return null;
    }

    /**
     * @return PartenaireLogo[]
     */
    public function findAllOrderByIdDesc(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'desc')
            ->getQuery()
            ->getResult();
    }
}
