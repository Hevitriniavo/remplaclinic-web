<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReferenceController extends AbstractController
{
    #[Route('/admin/reference', name: 'app_admin_reference')]
    public function index(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Référence',
        ];
        return $this->render('admin/reference.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
