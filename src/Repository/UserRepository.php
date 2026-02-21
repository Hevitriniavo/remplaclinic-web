<?php

namespace App\Repository;

use App\Common\DateUtil;
use App\Common\IdUtil;
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

    public function findByEmail(string $email): ?User
    {
        $query = $this->createQueryBuilder('u')
            ->distinct()
            ->addSelect('a')
            ->addSelect('s')
            ->addSelect('e')
            ->addSelect('sub')
            // ->addSelect('r')
            ->leftJoin('u.address', 'a')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.establishment', 'e')
            ->leftJoin('u.subscription', 'sub')
            // ->leftJoin('u.roles', 'r')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findAllDataTables(?int $roleId, DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['u.id', 'u.id', 'u.status', 'u.name', 'u.email', 'u.createAt', 's.name'], 'u.id');

        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.address', 'a')
            ->leftJoin('u.establishment', 'e')
            ->leftJoin('u.subscription', 'sub')
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
                        's.name LIKE :value',
                        'e.name LIKE :value',
                    )
                )
                ->setParameter('value', '%' . $params->value . '%');
        }

        // filters
        $filters = $params->filters;
        if (!empty($filters)) {
            // civility
            if (!empty($filters['civility'])) {
                $qb->andWhere('u.civility = :filter_civility')
                    ->setParameter('filter_civility', $filters['civility']);
            }

            // status
            if (isset($filters['status'])) {
                $qb->andWhere('u.status = :filter_status')
                    ->setParameter('filter_status', $filters['status']);
            }

            // current_speciality
            if (!empty($filters['current_speciality'])) {
                $qb->andWhere('u.currentSpeciality = :filter_current_speciality')
                    ->setParameter('filter_current_speciality', $filters['current_speciality']);
            }

            // mobilities
            if (!empty($filters['mobilities'])) {
                $qb
                    ->join('u.mobilities', 'm')
                    ->andWhere('m.id IN ('. IdUtil::implode($filters['mobilities']) . ')');
            }

            // specialities
            if (!empty($filters['specialities'])) {
                $qb->andWhere('s.id IN ('. IdUtil::implode($filters['specialities']) . ')');
            }

            // created_from
            if (!empty($filters['created_from'])) {
                $qb->andWhere('u.createAt >= :filter_created_from')
                    ->setParameter('filter_created_from', DateUtil::parseDate('d/m/Y', $filters['created_from'])->format('Y-m-d') . ' 00:00');
            }

            // created_to
            if (!empty($filters['created_to'])) {
                $qb->andWhere('u.createAt <= :filter_created_to')
                    ->setParameter('filter_created_to', DateUtil::parseDate('d/m/Y', $filters['created_to'])->format('Y-m-d') . ' 23:59');
            }

            // director
            if (!empty($filters['director'])) {
                $qb
                    ->join('u.directors', 'd')
                    ->andWhere('d.id IN ('. IdUtil::implode($filters['director']) . ')');
            }

            // clinic
            if (!empty($filters['clinic'])) {
                $qb
                    ->join('u.clinics', 'cl')
                    ->andWhere('cl.id IN ('. IdUtil::implode($filters['clinic']) . ')');
            }

            // installation_count_min
            if (!empty($filters['installation_count_min'])) {
                $qb->andWhere('sub.installationCount >= :filter_installation_count_min')
                    ->setParameter('filter_installation_count_min', $filters['installation_count_min']);
            }

            // installation_count_max
            if (!empty($filters['installation_count_max'])) {
                $qb->andWhere('sub.installationCount <= :filter_installation_count_max')
                    ->setParameter('filter_installation_count_max', $filters['installation_count_max']);
            }

            // abonnement_from
            if (!empty($filters['abonnement_from'])) {
                $qb->andWhere('sub.endAt >= :filter_abonnement_from')
                    ->setParameter('filter_abonnement_from', DateUtil::parseDate('d/m/Y', $filters['abonnement_from'])->format('Y-m-d') . ' 00:00');
            }

            // abonnement_to
            if (!empty($filters['abonnement_to'])) {
                $qb->andWhere('sub.endAt <= :filter_abonnement_to')
                    ->setParameter('filter_abonnement_to', DateUtil::parseDate('d/m/Y', $filters['abonnement_to'])->format('Y-m-d') . ' 23:59');
            }
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
        foreach ($paginator as $row) {
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
                foreach ($row->getMobilities() as $region) {
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

    public function findAllIdsForRequest(?int $roleId, array $params = [], array $exclus = []): array
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

        if (!empty($exclus)) {
            $exclus = array_map(fn($item) => (int) $item, $exclus);
            $qb->andWhere('u.id NOT IN (' . implode(',', $exclus) . ')');
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

    private function addQueryParameters(QueryBuilder $qb, array $params)
    {
        // @TODO: by postal_code?
        $speciality = empty($params['speciality']) ? '' : $params['speciality'];
        $mobility = empty($params['mobility']) ? '' : $params['mobility'];
        $subSpeciality = empty($params['sousSpeciality']) ? '' : $params['sousSpeciality'];
        $conditionTpe = empty($params['condition_type']) ? '' : $params['condition_type'];

        $expr = $conditionTpe === 'or' ? $qb->expr()->orX() : $qb->expr()->andX();

        if (!empty($speciality)) {

            $speciality = (int) $speciality;

            // ssr speciality: '285', '293', '289' => '301'
            $specialities = [];
            if ($speciality === 301) {
                $specialities = [285, 293, 289, 301];
            } else {
                $specialities = [$speciality];
            }

            $expr->add('s.id IN (' . implode(',', $specialities) . ')');
        }

        if (!empty($mobility)) {
            $mobility = (int) $mobility;

            // region europe: '504' => all => no criteria
            if ($mobility !== 504) {
                $expr->add('m.id = :mobility_id');

                $qb
                    ->setParameter('mobility_id', $mobility);
            }
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
        $qb = $this->_createNativeQuerySearch([
            'role' => $roleId,
        ]);

        $result = $qb
            ->select('COUNT(DISTINCT u.id) AS total')
            ->executeQuery()
            ->fetchOne();

        return  $result;
    }

    public function findAllByParams(array $params = []): array
    {
        $offset = empty($params['offset']) ? 0 : (int) $params['offset'];
        $limit = empty($params['limit']) ? 20 : (int) $params['limit'];

        $result = [
            'data' => [],
            'totalRecords' => 0
        ];

        $countQueryBuilder = $this->_createNativeQuerySearch($params);
        $listQueryBuilder = $this->_createNativeQuerySearch($params);


        $result['totalRecords'] = $countQueryBuilder
            ->select('COUNT(DISTINCT u.id) AS total')
            ->executeQuery()
            ->fetchOne();

        $queryResult = $listQueryBuilder
            ->leftJoin('u', 'user_speciality', 'us', 'us.user_id = u.id')
            ->leftJoin('us', 'speciality', 'sp', 'us.speciality_id = sp.id')
            ->select("u.id as id, CONCAT(u.surname, ' ', u.name) as prenom, u.current_speciality as statut, GROUP_CONCAT(DISTINCT sp.name) as sous_specialite")
            ->groupBy('u.id', 'u.name', 'u.surname')
            ->orderBy('u.id', 'desc')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->executeQuery();

        $allCurrentSpeciality = User::allCurrentSpecialities();

        while (($row = $queryResult->fetchAssociative()) !== false) {

            if (array_key_exists($row['statut'], $allCurrentSpeciality)) {
                $row['statut_name'] = $allCurrentSpeciality[$row['statut']];
            } else {
                $row['statut_name'] = '';
            }

            if (empty($row['sous_specialite'])) {
                $row['sous_specialite'] = '-';
            }

            $result['data'][] = $row;
        }

        return  $result;
    }

    /**
     * @return User[]
     */
    public function findLatestOrderByCreatedAt(int $size): array
    {
        return $this->createQueryBuilder('u')
            ->distinct()
            ->addSelect('a')
            ->addSelect('s')
            ->addSelect('e')
            ->addSelect('sub')
            ->addSelect('r')
            ->leftJoin('u.address', 'a')
            ->join('u.speciality', 's')
            ->leftJoin('u.establishment', 'e')
            ->leftJoin('u.subscription', 'sub')
            ->join('u.roles', 'r', Expr\Join::WITH, 'r.id = :role_replacement_id')
            ->where('u.status = :status_active')
            ->setParameter('status_active', true)
            ->setParameter('role_replacement_id', User::ROLE_REPLACEMENT_ID)
            ->orderBy('u.createAt', 'desc')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }

    private function _createNativeQuerySearch(array $params = [], bool $joinSpeciality = false, bool $joinRegion = false): NativeQueryBuilder
    {
        $qb = $this->getEntityManager()->getConnection()
            ->createQueryBuilder()
            ->from('user', 'u')
            ->where('u.status = 1');

        if (array_key_exists('role', $params)) {
            $qb->join('u', 'user_user_role', 'r', 'u.id = r.user_id AND r.user_role_id = :role_id')
                ->setParameter('role_id', $params['role']);
        }

        if (!empty($params['specialite'])) {
            // ssr speciality: '285', '293', '289' => '301'
            $specialities = [];
            if ($params['specialite'] === 301) {
                $specialities = [285, 293, 289, 301];
            } else {
                $specialities = [$params['specialite']];
            }

            $qb
                ->andWhere('u.speciality_id IN (' . implode(',', $specialities) .')');
        }

        // region europe: '504' => all => no criteria
        if (!empty($params['region']) && $params['region'] !== 504) {
            $qb
                ->join('u', 'user_region', 'ur', 'ur.user_id = u.id AND ur.region_id = :region_id')
                ->setParameter('region_id', $params['region'])
            ;
        } else if (!empty($params['search'])) {
            $qb
                ->join('u', 'user_region', 'ur', 'ur.user_id = u.id')
            ;
        }

        if (!empty($params['search'])) {
            $qb
                ->join('u', 'speciality', 's', 's.id = u.speciality_id')
                ->join('ur', 'region', 'rg', 'ur.region_id = rg.id')
                ->andWhere($qb->expr()->or(
                    's.name LIKE :search',
                    'rg.name LIKE :search'
                ))
                ->setParameter('search', '%' . $params['search'] . '%')
            ;
        }

        return $qb;
    }

    public function findAllForSelect(array $params = [])
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb->from('user', 'u')
            ->select('DISTINCT u.id, u.surname, u.name, ue.name AS establishmentName')
            ->join('u', 'user_user_role', 'uur', 'u.id = uur.user_id')
            ->leftJoin('u', 'user_establishment', 'ue', 'u.establishment_id = ue.id')
        ;

        // role
        if (!empty($params['role'])) {
            $qb->andWhere('uur.user_role_id = :role')
                ->setParameter('role', $params['role']);
        }

        // roles
        if (!empty($params['roles'])) {
            $in = array_map(fn($id) => (int) $id, $params['roles']);
            $qb->andWhere('uur.user_role_id IN ('. implode(',', $in) .')');
        }

        // exclusion
        if (!empty($params['exclus'])) {
            $in = array_map(fn($id) => (int) $id, $params['exclus']);
            $qb->andWhere('u.id NOT IN ('. implode(',', $in) .')');
        }

        // search
        $searchValue = empty($params['search']) ? '' : $params['search'];
        if (!empty($searchValue)) {
            $qb
                ->andWhere(
                    $qb->expr()->or(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        'ue.name LIKE :value'
                    )
                )
                ->setParameter('value', '%' . $searchValue . '%');
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }
}
