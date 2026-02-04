<?php

namespace App\Controller\Api;

use App\Dto\IdListDto;
use App\Repository\UserRepository;
use App\Security\SecurityUser;
use App\Service\User\UserDelete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {}

    #[Route('/api/users/{id}', name: 'api_user_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, UserDelete $userDelete): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /**
         * @var SecurityUser
         */
        $user = $this->getUser();
        if ($user->getUser()->getId() !== $id) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $deleted = $userDelete->remove($id);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->security->logout(false);
        }

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
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