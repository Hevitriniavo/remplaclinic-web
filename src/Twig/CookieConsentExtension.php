<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CookieConsentExtension extends AbstractExtension
{
    public function __construct(private RequestStack $requestStack) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cookie_consent', [$this, 'getConsent']),
        ];
    }

    public function getConsent(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request?->cookies->get('cookie_consent');
    }
}
