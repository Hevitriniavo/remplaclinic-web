<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    #[Route('/error/{code}', name: 'app_error_code')]
    public function showError($code): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error'.$code.'.html.twig', [
            'code' => $code,
        ]);
    }
}
