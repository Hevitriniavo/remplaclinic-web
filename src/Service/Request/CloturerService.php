<?php
namespace App\Service\Request;

use App\Entity\Request;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CloturerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {}

    public function execute(int $id): Request
    {
        // step 1: update statut
        $request = $this->updateStatus($id);

        return $request;
    }

    private function updateStatus(int $id): Request
    {
        // on update statut et request history
        /**
         * @var Request
         */
        $request = $this->em->find(Request::class, $id);
        if (is_null($request)){
            throw new Exception('No request found for #' . $id);
        }

        $request
            ->setStatus(Request::ARCHIVED);
        
        $this->em->flush();

        return $request;
    }
}