<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {}

    #[Route('/inscription', name: 'app_register', methods: ['GET'])]
    public function register(): Response
    {
        return $this->render('register/index.html.twig');
    }

    #[Route('/inscription/clinic', name: 'app_register_clinic', methods: ['GET'])]
    public function registerClinic(): Response
    {
        return $this->render('register/new_clinic.html.twig');
    }

    #[Route('/inscription/service-clinique', name: 'app_register_service_clinic', methods: ['GET'])]
    public function registerServiceClinic(): Response
    {
        return $this->render('register/new_service_clinic.html.twig');
    }

    #[Route('/inscription/doctor', name: 'app_register_doctor', methods: ['GET'])]
    public function registerDoctor(): Response
    {
        return $this->render('register/new_doctor.html.twig');
    }

    #[Route('/inscription/validation', name: 'app_register_success', methods: ['GET'])]
    public function registerValidationSuccess(): Response
    {
        $message1 = '';
        $message2 = '';
        if ($this->security->isGranted('ROLE_REPLACEMENT')) {
            $message1 = 'Votre compte a bien été créé !';
            $message2 = 'Si des demandes en cours correspondent à vos critères, vous allez recevoir un email. Vous pouvez retrouver ces différentes demandes depuis votre tableau de bord.';
        } else if ($this->security->isGranted('ROLE_CLINIC')) {
            $message1 = 'Votre compte clinique a bien été créé !';
            $message2 = "<b>Notre équipe va prendre contact avec vous directement pour le choix de votre abonnement. Si votre demande est urgente, vous pouvez joindre directement l'équipe au 06-69-05-33-00</b>";
        } else if ($this->security->isGranted('ROLE_DOCTOR')) {
            $message1 = 'Votre compte médecin a bien été créé !';
		    $message2 = "<b>Notre équipe va prendre contact avec vous directement pour le choix de votre abonnement. Si votre demande est urgente, vous pouvez joindre directement l'équipe au 06-69-05-33-00</b>";
        } else if ($this->security->isGranted('ROLE_DIRECTOR')) {
            $message1 = 'Votre compte directeur a bien été créé !';
		    $message2 = '';
        }

        return $this->render('register/register_success.html.twig', [
            'message1' => $message1,
            'message2' => $message2,
        ]);
    }
}
