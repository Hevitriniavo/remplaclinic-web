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
        $requestResponses = $this->getRequestResponses([$id]);

        if (count($requestResponses) > 0) {
            $this->em->remove($requestResponses[0]);
            
            $this->updateRequestResponseCount($requestResponses[0]);
            
            $this->em->flush();

            return $requestResponses[0];
        }

        return null;
    }

    public function deleteMultiple(array $ids): array
    {
        $result = $this->getRequestResponses($ids);

        foreach($result as $requestResponse) {
            $this->em->remove($requestResponse);
            $this->updateRequestResponseCount($requestResponse);
        }

        $this->em->flush();

        return $result;
    }

    private function getRequestResponses(array $ids): array
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

    private function updateRequestResponseCount(RequestResponse $requestResponse)
    {
        $request = $requestResponse->getRequest();

        if ($requestResponse->getStatus() === RequestResponse::ACCEPTE || $requestResponse->getStatus() === RequestResponse::PLUS_D_INFOS) {
            $request->decrementResponseCount();
        }
    }
}