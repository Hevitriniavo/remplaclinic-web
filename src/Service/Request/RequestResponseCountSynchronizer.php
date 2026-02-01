<?php
namespace App\Service\Request;

use App\Entity\RequestResponse;
use Doctrine\DBAL\Connection;

class RequestResponseCountSynchronizer
{
    public function __construct(
        private readonly Connection $connection
    )
    {}

    public function updateAllRequests(): int
    {
        $responseStatus = implode(',', [ RequestResponse::ACCEPTE, RequestResponse::PLUS_D_INFOS ]);
        $sql = "UPDATE request r SET r.response_count = (SELECT COUNT(p.id) FROM request_response p WHERE p.request_id = r.id AND p.status IN (". $responseStatus ."))";

        $stmt = $this->connection->prepare($sql);

        return $stmt->executeStatement();
    }
}