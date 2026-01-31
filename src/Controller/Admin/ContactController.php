<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/admin/contacts', name: 'app_admin_contacts')]
    public function index(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Contacts',
        ];
        return $this->render('admin/contact.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
