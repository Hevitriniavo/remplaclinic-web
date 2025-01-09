<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HowDoesItWorkController extends AbstractController
{
    #[Route('/comment-ca-marche', name: 'app_how_does_it_work')]
    public function index(): Response
    {
        return $this->render('how_does_it_work/index.html.twig');
    }
}
