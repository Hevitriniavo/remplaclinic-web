<?php

namespace App\Controller\Api;

use App\Service\User\UserDelete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, UserDelete $userDelete): Response
    {
        $deleted = $userDelete->remove($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }
}