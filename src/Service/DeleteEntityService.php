<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DeleteEntityService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $entityClass,
    )
    {}

    public function delete(int $id): mixed
    {   
        $entities = $this->getEntities([$id]);

        if (count($entities) > 0) {
            $this->em->remove($entities[0]);
            $this->em->flush();

            return $entities[0];
        }

        return null;
    }

    public function deleteMultiple(array $ids): array
    {
        $result = $this->getEntities($ids);

        foreach($result as $entities) {
            $this->em->remove($entities);
        }

        $this->em->flush();

        return $result;
    }

    private function getEntities(array $ids): array
    {
        $res = [];

        foreach($ids as $id) {
            $entities = $this->em->find($this->entityClass, $id);

            if (!$entities) {
                throw new Exception('No entity found for#' . $id);
            }

            $res[] = $entities;
        }

        return $res;
    }
}