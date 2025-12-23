<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Request>
 */
class RequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Request::class);
    }

    public function findAllDataTables(?RequestType $requestType, DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['u.id', 'u.status', 'u.requestType', 'a.name', 's.name', 'u.createdAt', 'u.startedAt', 'u.lastSentAt', 'responseCount'], 'u.id');
        
        $countResponseQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(RequestResponse::class, 'o')
            ->where('o.request = u.id')
            ->getDQL();


        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.region', 'r')
            ->leftJoin('u.applicant', 'a')
            ->leftJoin('u.responses', 'resp', 'resp.request = u.id AND res.status = ' . RequestResponse::ACCEPTED)
            ->addSelect('(' . $countResponseQuery . ') AS responseCount')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!is_null($requestType)) {
            $qb->where('u.requestType = :requestType')
                ->setParameter('requestType', $requestType);
        }
        
        if (!empty($params->value)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.title LIKE :value',
                        'u.startedAt LIKE :value',
                        'u.endAt LIKE :value',
                        'u.createdAt LIKE :value',
                        'a.name LIKE :value',
                        'a.surname LIKE :value',
                        'a.email LIKE :value',
                        's.name LIKE :value'
                    )
                )
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());
        
        $result = [];
        foreach($paginator as $request) {
            $result[] = [
                'request' => $request[0],
                'responseCount' => $request['responseCount'],
            ];
        }

        $response = DataTableResponse::fromPaginator($paginator, $params->draw + 1, true);
        $response->data = $result;
        return $response;
    }
}
