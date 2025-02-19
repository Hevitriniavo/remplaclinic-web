<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReplacementController extends AbstractController
{

    #[Route('/je-suis-un-remplacant', name: 'app_replacement')]
    public function index(): Response
    {
        return $this->render('replacement/index.html.twig');
    }


    #[Route('/je-cherche-un-remplacant', name: 'app_replacement_search')]
    public function searchReplacement(): Response
    {
        return $this->render('replacement/search.html.twig');
    }

    #[Route('/obtenir-sa-licence-de-remplacement', name: 'app_replacement_licence')]
    public function getLicence(): Response
    {
        return $this->render('replacement/licence.html.twig');
    }

}
