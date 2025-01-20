<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NodeController extends AbstractController
{
    #[Route('/node/990', name: 'app_node')]
    public function index(): Response
    {
        return $this->render('node/index.html.twig');
    }
}
