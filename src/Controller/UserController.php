<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/supprimer-compte', name: 'app_user_delete')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
