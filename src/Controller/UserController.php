<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/logout', name: 'app_user_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/user/supprimer-compte', name: 'app_user_delete')]
    public function deleteAccount(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
