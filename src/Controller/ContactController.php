<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contacts', name: 'app_contacts')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contacts/ouverture-compte', name: 'app_contacts_register_group_clinic')]
    public function contactCreateAccount(): Response
    {
        return $this->render('contact/contact-register.html.twig');
    }

    #[Route('/contacts/assistance', name: 'app_contacts_assistance')]
    public function contactAssistance(Request $request): Response
    {
        return $this->render('contact/contact-assistance.html.twig', [
            's' => $request->query->get('s'),
        ]);
    }
}
