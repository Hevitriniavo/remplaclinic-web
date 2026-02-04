<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\SpecialityDto;
use App\Entity\Speciality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Speciality>
 */
class SpecialityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Speciality::class);
    }

    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['s.id', 's.name', 'p.name'], 's.id');
        
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.specialityParent', 'p')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where('s.name LIKE :value')
                ->orWhere('p.name LIKE :value')
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function save(SpecialityDto $specialityDto) {
        $em = $this->getEntityManager();

        $speciality = new Speciality();
        
        $this->_setSpeciality($speciality, $specialityDto);

        $em->persist($speciality);
        $em->flush();

        return $speciality;
    }

    public function update(Speciality $speciality, SpecialityDto $specialityDto) {
        $em = $this->getEntityManager();

        $this->_setSpeciality($speciality, $specialityDto);

        $em->flush();

        return $speciality;
    }

    private function _setSpeciality(Speciality $speciality, SpecialityDto $specialityDto)
    {
        $speciality->setName($specialityDto->name);
        if (!empty($specialityDto->specialityParent)) {
            $specialityParent = $this->find($specialityDto->specialityParent);
            if (is_null($specialityParent)) {
                throw new EntityNotFoundException("L'ID specialite " . $specialityDto->specialityParent . " n'existe pas dans la base de donnees.");
            }
            $speciality->setSpecialityParent($specialityParent);
        }
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

    /**
     * @return Speciality[]
     */
    public function findAllSousSpecialite(int $id): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.specialityParent', 'p')
            ->where('p.id = :speciality_id')
            ->setParameter('speciality_id', $id)
            ->getQuery()
            ->getResult();
    }
}
