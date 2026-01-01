<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchReplacementController extends AbstractController
{
    #[Route('/rechercher-remplacants', name: 'search_replacement')]
    public function index (Request $request): Response
    {
        return $this->render('search_replacement/index.html.twig');
    }
}
