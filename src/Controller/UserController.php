<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {}

    #[Route('/user/logout', name: 'app_user_logout')]
    public function logout(): Response
    {
        $this->security->logout(false);

        return $this->redirectToRoute('app_home');
    }
}
