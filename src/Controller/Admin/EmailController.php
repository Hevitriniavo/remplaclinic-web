<?php

namespace App\Controller\Admin;

use App\Entity\EmailEvents;
use App\Repository\MailLogRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailController extends AbstractController
{
    #[Route('/admin/admin-emails', name: 'app_admin_emails_admin_list')]
    public function getAdminEmails(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Admin Emails',
        ];
        return $this->render('admin/mailbox/admin-emails.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/inboxs', name: 'app_admin_emails_inbox')]
    public function getInboxs(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Emails envoyes',
        ];
        return $this->render('admin/mailbox/inbox.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/inboxs/compose', name: 'app_admin_emails_compose')]
    public function composeInboxs(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Test envoi email',
        ];
        return $this->render('admin/mailbox/inbox-compose.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/inboxs/{id}/view', name: 'app_admin_emails_body_view', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function viewEmailBody(int $id, MailLogRepository $mailLogRepository): Response
    {
        $mailLog = $mailLogRepository->find($id);

        if (is_null($mailLog)) {
            throw new Exception('No mail log found for #'. $id);
        }

        return new Response($mailLog->getBody());
    }
}
