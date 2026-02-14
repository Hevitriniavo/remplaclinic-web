<?php

namespace App\Message;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class MessengerService
{
    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    public function getAllMessages(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['m.id', 'm.id', 'm.id', 'm.id', 'm.created_at', 'm.delivered_at'], 'm.id');
        
        $countQuery = $this->connection->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from('messenger_messages', 'm');
        
        $query = $this->connection->createQueryBuilder()
            ->select('m.id', 'm.body', 'm.headers', 'm.queue_name', 'm.created_at', 'm.available_at', 'm.delivered_at')
            ->from('messenger_messages', 'm')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset === null ? 0 : $params->offset);
        
        $this->addWhereClause($countQuery, $params->value);
        $this->addWhereClause($query, $params->value);
        
        $total = $countQuery->fetchOne();
        $records = $query->fetchAllAssociative();

        $messages = [];
        foreach ($records as $result) {
            $messageData = [
                'id' => $result['id'],
                'headers' => $result['headers'],
                'queue_name' => $result['queue_name'],
                'created_at' => $result['created_at'],
                'available_at' => $result['available_at'],
                'delivered_at' => $result['delivered_at'],
            ];

            $envelope = $this->unserializeSafe([
                'body' => $result['body'],
                'headers' => $result['headers'],
            ]);

            if ($envelope !== null) {
                $messageData['decoded'] = $envelope->getMessage();
                $messageData['type'] = $envelope->getMessage()::class;

                if (method_exists($envelope->getMessage(), '__toString')) {
                    $messageData['decoded_str'] = (string) $envelope->getMessage();
                }
            }

            $messages[] = $messageData;
        }

        $response = new DataTableResponse();
        $response->data = $messages;
        $response->draw = $params->draw;
        $response->recordsFiltered = $total;
        $response->recordsTotal = $total;
        
        return $response;
    }

    public function delete(int $id): ?int
    {   
        return $this->connection->createQueryBuilder()
            ->delete('messenger_messages')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeStatement();
    }

    public function deleteMultiple(array $ids): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->delete('messenger_messages');

        for($i = 0, $len = count($ids); $i < $len; $i++) {
            $qb->orWhere('id = :id_' . $i)
                ->setParameter('id_' . $i, $ids[$i]);
        }
        
        return $qb->executeStatement();
    }

    private function addWhereClause(QueryBuilder $query, $value)
    {
        if (!empty($value)) {
            $query->where($query->expr()->or(
                'm.body LIKE :value',
                'm.created_at LIKE :value',
            ))
                ->setParameter('value', '%' . $value . '%');
        }
    } 

    private function unserializeSafe(array $data): ?Envelope
    {
        if (empty($data)) {
            return null;
        }

        $serializer = new PhpSerializer();

        try {
            return $serializer->decode($data);
        } catch (\Throwable $e) {
            return null;
        }
    }
}