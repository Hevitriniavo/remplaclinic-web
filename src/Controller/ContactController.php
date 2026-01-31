<?php

namespace App\Controller;

use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
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

    #[Route('/contacts/confirmation', name: 'app_contacts_confirmation')]
    public function contactConfirmation(FlashBagAwareSessionInterface $flashBag): Response|RedirectResponse
    {
        $sessionVars = $flashBag->getFlashBag()->all();
        if (empty($sessionVars['contact_id']) || empty($sessionVars['contact_type'])) {
            return $this->redirectToRoute('app_contacts');
        }

        $contactType = (int) $sessionVars['contact_type'][0];

        $message1 = '';
        $message2 = '';
        if ($contactType === Contact::CONTACT_ASSISTANCE) {
            $message1 = 'Votre demande a bien été envoyée !';
            $message2 = "<b>Merci pour votre demande, l'équipe de Remplaclinic vous répondra dans les plus brefs délais.</b>";
        } else if ($contactType === Contact::CONTACT_OUVERTURE_COMPTE) {
            $message1 = 'Votre demande de création de compte a bien été prise en compte !';
            $message2 = "<b>L'équipe de Remplaclinic va prendre contact avec vous directement. Si votre demande est urgente, vous pouvez joindre directement l'équipe au 06-69-05-33-00.</b>";
        } else if ($contactType === Contact::CONTACT_INSTAL_CLINIC) {
            $message1 = 'Votre demande a bien été envoyée !';
		    $message2 = "<b>Merci pour votre demande de contact. L'équipe InstalClinic reviendra vers vous dans les plus brefs délais.</b>";
        } else {
            $message1 = 'Votre demande a bien été envoyée !';
		    $message2 = "<b>Merci pour votre demande de contact. Notre équipe reviendra vers vous dans les plus brefs délais.</b>";
        }
        return $this->render('contact/contact-confirmation.html.twig', [
            'message1' => $message1,
            'message2' => $message2,
        ]);
    }
}
