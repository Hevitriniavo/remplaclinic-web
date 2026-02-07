<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StaticPageController extends AbstractController
{
    #[Route('conditions-generales-dutilisation', name: 'app_cgu')]
    public function index(): Response
    {
        return $this->render('static_pages/cgu.html.twig');
    }

    #[Route('qui-sommes-nous', name: 'app_qui_sommes_nous')]
    public function quiSommesNous(): Response
    {
        return $this->render('static_pages/qui-sommes-nous.html.twig');
    }

    #[Route('decouvrez-nos-partenaires', name: 'app_decouvrez_nos_partenaires')]
    public function decouvrezNosPartenaires(): Response
    {
        return $this->render('static_pages/decouvrez-nos-partenaires.html.twig');
    }
}
