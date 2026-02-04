<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CookieConsentController
{
    #[Route('/cookie/consent', name: 'app_cookie_consent', methods: ['POST'])]
    public function consent(Request $request): JsonResponse
    {
        $value = $request->request->get('consent');

        if (!in_array($value, ['accepted', 'refused'], true)) {
            return new JsonResponse(['error' => 'Invalid value'], 400);
        }

        $response = new JsonResponse(['status' => 'ok']);

        $response->headers->setCookie(
            Cookie::create('cookie_consent')
                ->withValue($value)
                ->withExpires(strtotime('+6 months'))
                ->withPath('/')
                ->withSecure(true)      // HTTPS
                ->withHttpOnly(false)   // JS needs access
                ->withSameSite('lax')
        );

        return $response;
    }
}
