<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProtectionController extends AbstractController
{
    #[Route('/protegez-vous', name: 'app_protection')]
    public function index(): Response
    {
        return $this->render('protection/index.html.twig');
    }
}
