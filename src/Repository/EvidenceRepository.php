<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\EvidenceDto;
use App\Entity\Evidence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evidence>
 */
class EvidenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evidence::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['e.id', 'e.clinicName', 'e.specialityName', 'e.title', 'e.body'], 'e.id');
        
        $qb = $this->createQueryBuilder('e')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb
                ->orWhere('e.clinicName LIKE :value')
                ->orWhere('e.specialityName LIKE :value')
                ->orWhere('e.title LIKE :value')
                ->orWhere('e.body LIKE :value')
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function save(EvidenceDto $evidenceDto) {
        $em = $this->getEntityManager();

        $evidence = new Evidence();
        
        $this->_setEvidence($evidence, $evidenceDto);

        $em->persist($evidence);
        $em->flush();

        return $evidence;
    }

    public function update(Evidence $evidence, EvidenceDto $evidenceDto) {
        $em = $this->getEntityManager();

        $this->_setEvidence($evidence, $evidenceDto);

        $em->flush();

        return $evidence;
    }

    private function _setEvidence(Evidence $evidence, EvidenceDto $evidenceDto)
    {
        $evidence->setTitle($evidenceDto->title);
        $evidence->setSpecialityName($evidenceDto->specialityName);
        $evidence->setClinicName($evidenceDto->clinicName);
        $evidence->setBody($evidenceDto->body);
    }

    public function remove(int $id) {
        $em = $this->getEntityManager();

        $evidence = $this->find($id);
        
        if (!is_null($evidence)) {
            $em->remove($evidence);
            $em->flush();
            return true;
        }

        return false;
    }

    /**
     * @return Evidence[]
     */
    public function findAllOrderByIdDesc(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'desc')
            ->getQuery()
            ->getResult();
    }
}
