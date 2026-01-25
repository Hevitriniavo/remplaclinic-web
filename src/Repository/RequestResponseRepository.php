<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findAllDataTables(int $requestId, DataTableParams $params): DataTableResponse
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
                'statut' => $row->getStatusAsText(),
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
}
