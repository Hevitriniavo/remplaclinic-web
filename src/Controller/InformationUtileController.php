<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InformationUtileController extends AbstractController
{
    #[Route('/infos-utiles', name: 'app_information_utile')]
    public function index(): Response
    {
        return $this->render('information_utile/index.html.twig');
    }
}
