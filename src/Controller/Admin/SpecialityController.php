<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpecialityController extends AbstractController
{
    #[Route('/admin/speciality', name: 'app_admin_speciality')]
    public function index(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Spécialité',
        ];
        return $this->render('admin/speciality.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
