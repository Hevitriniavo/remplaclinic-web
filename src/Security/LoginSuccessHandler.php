<?php
namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $firewallName = $this->security->getFirewallConfig($request)?->getName();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $this->security->getUser();

        $hasAdminAccess = $this->security->isGranted('ROLE_ADMIN', $user);

        if ($hasAdminAccess) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin_home'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_user_espace_perso'));
    }
}
