<?php

namespace App\Controller\Api;

use App\Dto\IdListDto;
use App\Repository\UserRepository;
use App\Service\User\UserDelete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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

    #[Route('/api/users/delete-multiple', name: 'api_user_delete_multiple', methods: ['DELETE'])]
    public function removeMultiple(
        UserDelete $userDelete,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $users
    ): Response
    {
        $deleted = $userDelete->removeMultiple($users->ids);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/users/for-select', name: 'api_user_liste_select', methods: ['GET'])]
    public function getAllForSelect(Request $request, UserRepository $userRepository): Response
    {
        $params = $request->query->all();

        return $this->json($userRepository->findAllForSelect($params));
    }
}