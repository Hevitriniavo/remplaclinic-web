<?php
namespace App\Service\Request;

use App\Entity\RequestResponse;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DeleteRequestResponseService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {}

    public function delete(int $id): ?RequestResponse
    {   
        $requestResponses = $this->getMailLogs([$id]);

        if (count($requestResponses) > 0) {
            $this->em->remove($requestResponses[0]);
            $this->em->flush();

            return $requestResponses[0];
        }

        return null;
    }

    public function deleteMultiple(array $ids): array
    {
        $result = $this->getMailLogs($ids);

        foreach($result as $requestResponse) {
            $this->em->remove($requestResponse);
        }

        $this->em->flush();

        return $result;
    }

    private function getMailLogs(array $ids): array
    {
        $res = [];

        foreach($ids as $id) {
            $requestResponse = $this->em->find(RequestResponse::class, $id);

            if (!$requestResponse) {
                throw new Exception('No request response found for #' . $id);
            }

            $res[] = $requestResponse;
        }

        return $res;
    }
}