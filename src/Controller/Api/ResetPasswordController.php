<?php

namespace App\Controller\Api;

use App\Dto\ResetPassword\ResetPasswordEmailDto;
use App\Dto\ResetPassword\ResetPasswordNewPasswordDto;
use App\Service\User\ResetPasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly ResetPasswordService $resetPasswordService
    ) {}

    #[Route('api/reset-password/link', name: 'api_reset_password_link')]
    public function resetPasswordLink(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ResetPasswordEmailDto $resetPassword
    ): Response
    {
        $this->resetPasswordService->genererateCode($resetPassword->email);

        return $this->json([
            '_redirect' => $this->generateUrl('app_reset_password_link')
        ]);
    }

    #[Route('api/reset-password/new-password', name: 'api_reset_password_new')]
    public function resetPasswordNewPassword(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ResetPasswordNewPasswordDto $resetPassword
    ): Response
    {
        $this->resetPasswordService->updatePassword($resetPassword->code, $resetPassword->password);

        return $this->json([
            '_redirect' => $this->generateUrl('app_reset_password_new_success'),
        ]);
    }
}
