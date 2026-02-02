<?php

namespace App\Controller\Admin;

use App\Entity\RequestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/admin/', name: 'app_admin_home')]
    public function index(): Response
    {
        $breadcrumbs = [
            'Dashboard',
        ];
        
        return $this->render('admin/home/index.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'debutMois' => date('01/m/Y'),
            'responseStatus' => RequestResponse::ACCEPTE,
        ]);
    }
}
