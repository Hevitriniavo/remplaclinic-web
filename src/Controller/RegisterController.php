<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function create(): Response
    {
        return $this->render('register/index.html.twig');
    }


    #[Route('/clinic', name: 'app_register_clinic')]
    public function createClinic(): Response
    {
        return $this->render('register/new_clinic.html.twig');
    }

    #[Route('/doctor', name: 'app_register_doctor')]
    public function createDoctor(): Response
    {
        return $this->render('register/new_doctor.html.twig');
    }

    #[Route('/clinic/service', name: 'app_register_service_clinic')]
    public function createServiceClinic(): Response
    {
        return $this->render('register/new_service_clinic.html.twig');
    }
}
