<?php

namespace App\Repository;

use App\Common\DateUtil;
use App\Common\IdUtil;
use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder as NativeQueryBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequestResponse>
 */
class RequestResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestResponse::class);
    }

    /**
     * List of request responses
     */
    public function findAllDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['a.id', 'a.id', 'a.status', 'r.request_type', 'c.id', 'r.id', 'r.created_at', 'u.id', 'a.updated_at'], 'a.id');
        
        $paramsArr = [
            'value' => $params->value,
            'filters' => $params->filters,
        ];

        $queryBuilder = $this->createNativeFindAllQueryBuilder($paramsArr);
        
        $countQueryBuilder = clone $queryBuilder;
        $totalRows = $countQueryBuilder->select('COUNT(DISTINCT a.id) as total')
            ->executeQuery()
            ->fetchNumeric();
        
        $data = $queryBuilder
            ->leftJoin('c', 'user_user_role', 'uur', 'c.id = uur.user_id AND uur.user_role_id IN ('. implode(', ', [User::ROLE_CLINIC_ID, User::ROLE_DOCTOR_ID]) . ')')
            ->leftJoin('c', 'user_establishment', 'ue', 'c.establishment_id = ue.id')
            ->addSelect(
                'a.id AS id',
                'a.status AS status',
                'a.updated_at AS updated_at'
            )
            ->addSelect(
                'u.id AS user_id',
                'u.name AS user_name',
                'u.surname AS user_surname'
            )
            ->addSelect(
                'r.id AS request_id',
                'r.request_type AS request_type',
                'r.title AS request_title',
                'r.created_at AS request_created_at'
            )
            ->addSelect(
                'c.id AS applicant_id',
                'c.name AS applicant_name',
                'c.surname AS applicant_surname',
                'uur.user_role_id AS applicant_role_id',
                'ue.name AS applicant_establishment_name',
            )
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset ? $params->offset : 0)
            ->executeQuery()
            ->fetchAllAssociative();

        return DataTableResponse::make($data, $totalRows[0], $params->draw + 1);
    }

    /**
     * List of response for a given request. Shown in request detail tab.
     */
    public function findAllDataTablesForRequest(int $requestId, DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['a.id', 'a.id', 'u.name', 'u.surname', 's.name', 'a.status'], 'a.id');

        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('a.request', 'r')
            ->where('r.id = :requestId')
            ->setParameter('requestId', $requestId)
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);
        
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
        
        $result = [];
        foreach($paginator as $row) {
            $result[] = [
                'id' => $row->getId(),
                'statut' => $row->getStatus(),
                'user' => [
                    'id' => $row->getUser()->getId(),
                    'name' => $row->getUser()->getName(),
                    'surname' => $row->getUser()->getSurname(),
                    'speciality' => [
                        'id' => $row->getUser()->getSpeciality()->getId(),
                        'name' => $row->getUser()->getSpeciality()->getName(),
                    ],
                ],
            ];
        }

        $response = DataTableResponse::fromPaginator($paginator, $params->draw + 1, true);
        $response->data = $result;
        return $response;
    }

    /**
     * List of user not added to a request.
     */
    public function findAllUserNotAddedTo(int $requestId, ?string $searchTerm = ''): array
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb
            ->select('u.id, u.name, u.surname')
            ->from('user', 'u')
            ->join('u', 'user_user_role', 'r', 'r.user_id = u.id')
            ->leftJoin('u', 'request_response', 'rr', 'rr.user_id = u.id AND rr.request_id = :request_id')
            ->where('rr.id IS NULL')
            ->andWhere('u.status = 1')
            ->andWhere('r.user_role_id =' . User::ROLE_REPLACEMENT_ID)
            ->setParameter('request_id', $requestId)
        ;

        if (!empty($searchTerm)) {
            $qb
                ->andWhere($qb->expr()->or(
                    'u.name LIKE :search_value',
                    'u.surname LIKE :search_value',
                    'u.email LIKE :search_value'
                ))
                ->setParameter('search_value', '%' . $searchTerm .'%')
            ;
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Find user id matched to a given request
     * 
     * @param int $requestId
     * 
     * @return array Liste des ids utilisateur (on prend seulement id pour de raison de performence en memoire).
     */
    public function findAllUserIdsFor(int $requestId): array
    {
        // @TODO: what if user delete? change region? change speciality?
        // @TODO: what if request change speciality? region?
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb
            ->select('c.user_id')
            ->from('request_response', 'c')
            ->where('c.request_id = :request_id')
            ->setParameter('request_id', $requestId)
            ->orderBy('c.user_id', 'desc')
        ;

        return $qb->executeQuery()->fetchFirstColumn();
    }

    /**
     * Get all users who apply for the request
     * 
     * @param int $requestId
     * 
     * @return User[]
     */
    public function findAllUserWhoAccept(int $requestId): array
    {
        $responses = $this->createQueryBuilder('a')
            ->join('a.user', 'u')
            ->join('a.request', 'r')
            ->where('a.status = :status')
            ->andWhere('r.id  = :request_id')
            ->setParameter('status', RequestResponse::ACCEPTE)
            ->setParameter('request_id', $requestId)
            ->orderBy('a.updatedAt', 'desc')
            ->getQuery()
            ->getResult();

        return array_map(fn(RequestResponse $item) => $item->getUser(), $responses);
    }

    /**
     * List of request response connected to a given user.
     */
    public function findAllByUserId(int $userId, RequestType $requestType, int $limit = 10, int $offset = 0, ?int $status = null): DataTableResponse
    {
        $params = [
            'user' => $userId,
            'request_type' => $requestType,
            'exclu_request_status' => Request::ARCHIVED,
            'status' => $status,
        ];

        $paginator = new Paginator($this->createFindAllQueryBuilder($params, $limit, $offset)->getQuery());

        return DataTableResponse::fromPaginator($paginator, 0);
    }

    /**
     * List of request response connected to a given request. Shown in request detail for clinic and doctor tab.
     */
    public function findAllByRequestId(int $requestId, int $limit = 10, int $offset = 0, ?int $status = null): array
    {
        $params = [
            'request' => $requestId,
            'status' => $status,
        ];

        $qb = $this->createFindAllQueryBuilder($params, $limit, $offset);

        $paginator = new Paginator($qb->getQuery());

        $result = [
            'totalRecords' => count($paginator),
            'data' => [],
        ];

        foreach($paginator as $row) {
            $result['data'][] = [
                'id' => $row->getId(),
                'statut' => $row->getApplicantStatusAsText(),
                'user' => [
                    'id' => $row->getUser()->getId(),
                    'name' => $row->getUser()->getSurnameAndName(),
                    'current_speciality' => $row->getUser()->getCurrentSpecialityAsText(),
                    'sous_specialite' => $row->getUser()->getSubSpecialitiesAsText(),
                ],
            ];
        }

        return $result;
    }

    private function createFindAllQueryBuilder(array $params = [], int $limit = 10, int $offset = 0): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('p.request', 'r')
            ->join('r.applicant', 'a')
        ;
        
        // user
        if (!empty($params['user'])) {
            $qb->andWhere('u.id = :user_id')
                ->setParameter('user_id', $params['user']);
        }

        // request
        if (!empty($params['request'])) {
            $qb->andWhere('r.id = :request_id')
                ->setParameter('request_id', $params['request']);
        }

        // request_type
        if (!empty($params['request_type'])) {
            $qb->andWhere('r.requestType = :request_type')
                ->setParameter('request_type', $params['request_type']);
        }

        // exclu request statut
        if (!empty($params['exclu_request_status'])) {
            $qb->andWhere('r.status <> :exclu_request_status')
                ->setParameter('exclu_request_status', $params['exclu_request_status']);
        }
        
        if (!empty($params['status'])) {
            $qb->andWhere('p.status = :p_status')
                ->setParameter('p_status', $params['status']);
        } else {
            $qb->andWhere('p.status <> :p_status')
                ->setParameter('p_status', RequestResponse::EXCLU);
        }

        $qb
            ->orderBy('r.createdAt', 'desc')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb;
    }

    public function findOneByUserIdAndRequestId(int $userId, int $requestId): ?RequestResponse
    {
        return $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->join('p.request', 'r')
            ->join('r.applicant', 'a')
            ->andWhere('u.id = :user_id AND r.id = :request_id')
            ->setParameter('user_id', $userId)
            ->setParameter('request_id', $requestId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createNativeFindAllQueryBuilder(array $params = []): NativeQueryBuilder
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb->from('request_response', 'a')
            ->leftJoin('a', 'user', 'u', 'a.user_id = u.id')
            ->leftJoin('a', 'request', 'r', 'a.request_id = r.id')
            ->leftJoin('r', 'user', 'c', 'r.applicant_id = c.id');

        if (!empty($params['value'])) {
            $qb
                ->andWhere(
                    $qb->expr()->or(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        'c.name LIKE :value',
                        'c.surname LIKE :value',
                        'c.email LIKE :value'
                    )
                )
                ->setParameter('value', '%' . $params['value'] . '%');
        }

        if (!empty($params['filters'])) {
            $filters = $params['filters'];

            // user
            if (!empty($filters['user'])) {
                $qb->andWhere('u.id IN ('. IdUtil::implode($filters['user']) . ')');
            }

            // applicant
            if (!empty($filters['applicant'])) {
                $qb->andWhere('c.id IN ('. IdUtil::implode($filters['applicant']) . ')');
            }

            // status
            if (isset($filters['status'])) {
                $qb->andWhere('a.status = :filter_status')
                    ->setParameter('filter_status', $filters['status']);
            }

            // request_type
            if (isset($filters['request_type'])) {
                $qb->andWhere('r.request_type = :filter_request_type')
                    ->setParameter('filter_request_type', $filters['request_type']);
            }

            // regions
            if (!empty($filters['regions'])) {
                $qb
                    ->leftJoin('r', 'region', 'rg', 'r.region_id = rg.id')
                    ->andWhere('rg.id IN ('. IdUtil::implode($filters['regions']) . ')');
            }

            // specialities
            if (!empty($filters['specialities'])) {
                $qb
                    ->leftJoin('r', 'speciality', 'sp', 'r.speciality_id = sp.id')
                    ->andWhere('sp.id IN ('. IdUtil::implode($filters['specialities']) . ')');
            }

            // created_from
            if (!empty($filters['created_from'])) {
                $qb->andWhere('r.created_at >= :filter_created_from')
                    ->setParameter('filter_created_from', DateUtil::parseDate('d/m/Y', $filters['created_from'])->format('Y-m-d') . ' 00:00');
            }

            // created_to
            if (!empty($filters['created_to'])) {
                $qb->andWhere('r.created_at <= :filter_created_to')
                    ->setParameter('filter_created_to', DateUtil::parseDate('d/m/Y', $filters['created_to'])->format('Y-m-d') . ' 23:59');
            }

            // updated_from
            if (!empty($filters['updated_from'])) {
                $qb->andWhere('a.updated_at >= :filter_updated_from')
                    ->setParameter('filter_updated_from', DateUtil::parseDate('d/m/Y', $filters['updated_from'])->format('Y-m-d') . ' 00:00');
            }

            // updated_to
            if (!empty($filters['updated_to'])) {
                $qb->andWhere('a.updated_at <= :filter_updated_to')
                    ->setParameter('filter_updated_to', DateUtil::parseDate('d/m/Y', $filters['updated_to'])->format('Y-m-d') . ' 23:59');
            }
        }

        return $qb;
    }
}
