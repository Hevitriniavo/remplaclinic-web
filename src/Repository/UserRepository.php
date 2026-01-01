<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder as NativeQueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllDataTables(?int $roleId, DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['u.id', 'u.id', 'u.status', 'u.name', 'u.email', 'u.createAt', 's.name'], 'u.id');
        
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!is_null($roleId)) {
            $qb->join('u.roles', 'r', Expr\Join::WITH, 'r.id = :roleId')
                ->setParameter('roleId', $roleId);
        }
        
        if (!empty($params->value)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        's.name LIKE :value'
                    )
                )
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function findAllDataTablesForRequest(?int $roleId, ?DataTableParams $params = null, array $queryParams = []): DataTableResponse
    {
        $searchParam = empty($queryParams['search']) ? '' : $queryParams['search'];

        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.mobilities', 'm')
            ->andWhere('u.status = 1');
        
        if (!is_null($params)) {
            $sortBy = $params->getOrderColumn(['u.id', 'u.name', 'u.surname', 's.name', 'm.name'], 'u.id');
            $searchParam = $params->value;

            $qb->orderBy($sortBy, $params->getOrderDir())
                ->setMaxResults($params->limit)
                ->setFirstResult($params->offset);
        }

        if (!is_null($roleId)) {
            $qb->join('u.roles', 'r')
                ->andWhere('r.id = :roleId')
                ->setParameter('roleId', $roleId);
        }
        
        if (!empty($searchParam)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        's.name LIKE :value',
                        'm.name LIKE :value',
                    )
                )
                ->setParameter('value', '%' . $searchParam . '%');
        }

        $this->addQueryParameters($qb, $queryParams);
        
        $paginator = new Paginator($qb->getQuery());

        $result = [];
        foreach($paginator as $row) {
            $resultRow = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'surname' => $row->getSurname(),
                'speciality' => [
                    'id' => $row->getSpeciality()?->getId(),
                    'name' => $row->getSpeciality()?->getName(),
                ],
                'mobilities' => []
            ];

            if ($row->getMobilities()) {
                foreach($row->getMobilities() as $region) {
                    $resultRow['mobilities'][] = [
                        'id' => $region->getId(),
                        'name' => $region->getName(),
                    ];
                }
            }

            $result[] = $resultRow;
        }

        $response = DataTableResponse::fromPaginator($paginator, $params->draw + 1, true);
        $response->data = $result;
        return $response;
    }

    public function findAllIdsForRequest(?int $roleId, array $params = []): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('DISTINCT(u.id)')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.mobilities', 'm')
            ->andWhere('u.status = 1');

        if (!is_null($roleId)) {
            $qb->join('u.roles', 'r')
                ->andWhere('r.id = :roleId')
                ->setParameter('roleId', $roleId);
        }
        
        $searchValue = empty($params['search']) ? '' : $params['search'];
        if (!empty($searchValue)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        's.name LIKE :value',
                        'm.name LIKE :value',
                    )
                )
                ->setParameter('value', '%' . $searchValue . '%');
        }

        $this->addQueryParameters($qb, $params);
        
        return $qb->getQuery()->getSingleColumnResult();
    }

    private function addQueryParameters(QueryBuilder $qb, array $params) {
        $speciality = empty($params['speciality']) ? '' : $params['speciality'];
        $mobility = empty($params['mobility']) ? '' : $params['mobility'];
        $subSpeciality = empty($params['sousSpeciality']) ? '' : $params['sousSpeciality'];
        $conditionTpe = empty($params['condition_type']) ? '' : $params['condition_type'];

        $expr = $conditionTpe === 'or' ? $qb->expr()->orX() : $qb->expr()->andX();

        if (!empty($speciality)) {
            $expr->add('s.id = :speciality_id');

            $qb
                ->setParameter('speciality_id', $speciality);
        }

        if (!empty($mobility)) {
            $expr->add('m.id = :mobility_id');

            $qb
                ->setParameter('mobility_id', $mobility);
        }

        $subSpeciality = empty($params['sousSpeciality']) ? '' : $params['sousSpeciality'];
        if (!empty($subSpeciality)) {
            $sousSpecialityQb = $this->createQueryBuilder('subU')
                ->select('DISTINCT(subU.id)')
                ->leftJoin('subU.subSpecialities', 'sub')
                ->andWhere('sub.id = :sub_speciality_id')
                ->getDQL();
            
            $expr->add('u.id IN (' . $sousSpecialityQb . ')');

            $qb
                ->setParameter('sub_speciality_id', $subSpeciality);
        }

        $qb->andWhere($expr);
    }

    public function findAllByRole(?int $roleId): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.roles', 'r', Join::ON, 'r.id = :role_id')
            ->setParameter('role_id', $roleId);
        
        return $qb->select('a')
            ->getQuery()
            ->getResult();
    }

    public function countAllByRole(?int $roleId): int
    {
        $qb = $this->_createNativeQuerySearch($roleId);

        $result = $qb
            ->select('COUNT(u.id) AS total')
            ->executeQuery()
            ->fetchNumeric()
        ;

        return  $result[0];
    }

    /**
     * @return User[]
     */
    public function findLatestOrderByCreatedAt(int $size): array
    {
        return $this->createQueryBuilder('u')
            ->join('u.speciality', 's')
            ->join('u.roles', 'r', Expr\Join::WITH, 'r.id = :role_replacement_id')
            ->where('u.status = :status_active')
            ->setParameter('status_active', true)
            ->setParameter('role_replacement_id', User::ROLE_REPLACEMENT_ID)
            ->orderBy('u.createAt', 'desc')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }

    private function _createNativeQuerySearch(?int $role = User::ROLE_REPLACEMENT_ID, ?int $regionId = null, ?int $specialityId = null): NativeQueryBuilder
    {
        $qb = $this->getEntityManager()->getConnection()
            ->createQueryBuilder()
            ->from('user', 'u')
            ->join('u', 'user_user_role', 'r', 'u.id = r.user_id AND r.user_role_id = :role_id')
            ->where('u.status = 1')
            ->setParameter('role_id', $role);
        
        if (!is_null($specialityId)) {
            $qb
                ->where('u.speciality_id = :speciality_id')
                ->setParameter('speciality_id', $specialityId)
            ;
        }

        if (!is_null($regionId)) {
            $qb
                ->join('u', 'user_region', 'ur', 'ur.user_id = u.id AND u.region_id = :region_id')
                ->setParameter('region_id', $regionId)
            ;
        }

        return $qb;
    }
}
