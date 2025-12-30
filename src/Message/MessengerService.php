<?php

namespace App\Message;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

class MessengerService
{
    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    public function getAllMessages(): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id', 'body', 'headers', 'queue_name', 'created_at', 'available_at', 'delivered_at')
            ->from('messenger_messages');

        $results = $query->executeQuery()->fetchAllAssociative();

        $messages = [];
        foreach ($results as $result) {
            $messageData = [
                'id' => $result['id'],
                'body' => $result['body'],
                'headers' => $result['headers'],
                'queue_name' => $result['queue_name'],
                'created_at' => $result['created_at'],
                'available_at' => $result['available_at'],
                'delivered_at' => $result['delivered_at'],
                // 'redelivered' => $result['redelivered'],
                // 'priority' => $result['priority'],
                // 'attempts' => $result['attempts'],
            ];

            $envelope = $this->unserializeSafe([
                'body' => $result['body'],
                'headers' => $result['headers'],
            ]);

            if ($envelope !== null) {
                $messageData['decoded'] = $envelope->getMessage();
                $messageData['type'] = $envelope->getMessage()::class;
            }

            $messages[] = $messageData;
        }
        return $messages;
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