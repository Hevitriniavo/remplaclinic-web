<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
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
        $sortBy = $params->getOrderColumn(['u.id', 'u.id', 'u.status', 'u.requestType', 'a.name', 's.name', 'u.createdAt', 'u.startedAt', 'u.lastSentAt', 'responseCount'], 'u.id');
        
        $countResponseQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(RequestResponse::class, 'o')
            ->where('o.request = u.id')
            ->andWhere('o.status = ' . RequestResponse::ACCEPTE)
            ->getDQL();

        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.region', 'r')
            ->leftJoin('u.applicant', 'a')
            ->leftJoin('a.establishment', 'es')
            // ->leftJoin('u.responses', 'resp', 'resp.request = u.id AND res.status = ' . RequestResponse::ACCEPTE)
            ->addSelect('(' . $countResponseQuery . ') AS responseCount')
            // ->addSelect('0 AS responseCount')
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
                        'u.id LIKE :value',
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

    /**
     * @return Request[]
     */
    public function findLatestByCreatedAt(int $size): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.applicant', 'a')
            ->where('u.status = :status_in_progress')
            ->setParameter('status_in_progress', Request::IN_PROGRESS)
            ->andWhere('u.requestType = :request_type')
            ->setParameter('request_type', RequestType::REPLACEMENT)
            ->orderBy('u.createdAt', 'desc')
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all request by the given params
     * 
     * @param array<string,string>
     * 
     * @return Request[]|int[]
     */
    public function findAllBy(array $params = [], bool $onlyId = false): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.speciality', 's')
            ->leftJoin('r.applicant', 'a')
            ->leftJoin('r.region', 'rg')
        ;

        // speciality
        $speciality = null;
        if (array_key_exists('speciality', $params)) {
            $speciality = (int) $params['speciality'];
        }

        // ssr speciality management
        // '285', '293', '289' => '301'
        $specialities = [];
        if (in_array($speciality, [285, 293, 289])) {
            $specialities = [$speciality, 301];
        // NB: The inverse does not correct => an user with 301 is not able to 285, 293, 289
        // } else if ($speciality === 301) {
        //     $specialities = [$speciality, 285, 293, 289];
        } else if (!is_null($speciality)) {
            $specialities = [$speciality];
        }

        if (array_key_exists('specialities', $params)) {
            $criteriaSpecialities = array_map(fn($id) => (int) $id, $params['specialities']);
            $specialities = array_unique(array_merge($specialities, $criteriaSpecialities));
        }

        if (!empty($specialities)) {
            $qb->andWhere('s.id IN (' . implode(',', $specialities) . ')');
        }

        // region
        $region = null;
        if (array_key_exists('region', $params)) {
            $region = (int) $params['region'];
        }

        // region europe management
        // '504' => all
        $regions = [];
        if ($region === 504) {
            // europe => all => no region criteria
            $regions = [];
        } else {

            // if given then add europe
            if (!is_null($region)) {
                $regions = [$region];
            }

            if (array_key_exists('regions', $params)) {
                $criteriaRegions = array_map(fn($id) => (int) $id, $params['regions']);
                $regions = array_merge($regions, $criteriaRegions);
            }

            array_push($regions, 504);

            $regions = array_unique($regions);
        }


        if (!empty($regions)) {
            $qb->andWhere('rg.id IN (' . implode(',', $regions) . ')');
        }

        // statut
        $status = null;
        if (array_key_exists('status', $params)) {
            $status = (int) $params['status'];
        }

        // archived statut management
        if (is_null($status)) {
            $qb->andWhere('r.status <> :status')
                ->setParameter('status', Request::ARCHIVED);
        } else {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        if ($onlyId) {
            return $qb->select('r.id')
                ->getQuery()
                ->getSingleColumnResult()
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllByUserId(int $userId, RequestType $requestType, int $limit = 10, int $offset = 0, ?int $status = null): DataTableResponse
    {
        $countResponseQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(RequestResponse::class, 'o')
            ->where('o.request = r.id')
            ->andWhere('o.status IN (' . implode(',', [RequestResponse::ACCEPTE, RequestResponse::PLUS_D_INFOS]) . ')')
            ->getDQL()
        ;
    
        $qb = $this->createQueryBuilder('r')
            ->select('r AS request', '(' . $countResponseQuery . ') AS responseCount')
            ->join('r.applicant', 'a')
        ;
            
        $qb->where('a.id = :applicant_id')
            ->setParameter('applicant_id', $userId)
            ->andWhere('r.requestType = :request_type')
            ->setParameter('request_type', $requestType)
            ->orderBy('r.createdAt', 'desc')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        if (!is_null($status)) {
            $qb->andWhere('r.status = :r_status')
                ->setParameter('r_status', $status);
        }

        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, 0);
    }
}
