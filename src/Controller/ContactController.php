<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contacts', name: 'app_contacts')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function renderContact(): Response
    {
        return $this->render('contact/contact.html.twig');
    }
}
