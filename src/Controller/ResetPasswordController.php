<?php

namespace App\Controller;

use App\Service\User\ResetPasswordService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly ResetPasswordService $resetPasswordService,
    ) {}

    #[Route('reset-password', name: 'app_reset_password')]
    public function index(): Response
    {
        return $this->render('reset-password/index.html.twig');
    }

    #[Route('reset-password/link', name: 'app_reset_password_link')]
    public function resetPasswordLink(): Response
    {
        return $this->render('reset-password/link_success.html.twig');
    }

    #[Route('reset-password/new-password/{code}', name: 'app_reset_password_new')]
    public function resetPasswordNewPassword(string $code): Response
    {
        $viewData = [];
        try {
            $activeToken = $this->resetPasswordService->checkCode($code);
            $viewData['token'] = $activeToken;
        } catch (Exception $e) {
            $viewData['msg'] = $e->getMessage();
        }
        return $this->render('reset-password/new_password.html.twig', $viewData);
    }

    #[Route('reset-password/confirmation', name: 'app_reset_password_new_success')]
    public function resetPasswordSuccess(): Response
    {
        // @TODO: process change password here
        return $this->render('reset-password/new_password_success.html.twig');
    }
}
