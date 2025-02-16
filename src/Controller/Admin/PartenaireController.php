<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PartenaireController extends AbstractController
{
    #[Route('/admin/partenaire', name: 'app_admin_partenaire')]
    public function index(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Partenaire',
        ];
        return $this->render('admin/partenaire.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
