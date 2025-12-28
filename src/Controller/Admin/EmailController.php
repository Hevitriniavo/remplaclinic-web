<?php

namespace App\Controller\Admin;

use App\Entity\EmailEvents;
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
}
