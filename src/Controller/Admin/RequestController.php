<?php

namespace App\Controller\Admin;

use App\Repository\RequestRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RequestController extends AbstractController
{
    public function __construct(private RequestRepository $requestRepository) {}

    #[Route('/admin/request-replacements', name: 'app_admin_request_replacement')]
    public function replacement(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Demande de remplaçement');
        return $this->render('admin/request/replacement.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/request-installations', name: 'app_admin_request_installation')]
    public function installation(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs("Proposition d'installation");
        return $this->render('admin/request/installation.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/request-replacements/new', name: 'app_admin_request_replacement_new')]
    public function newReplacement(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Demande de remplaçement');
        $breadcrumbs[1] = [
            'url' => $this->generateUrl('app_admin_request_replacement'),
            'text' => 'Demande de remplaçement',
        ];
        $breadcrumbs[] = 'Nouvelle demande de remplaçement';
        return $this->render('admin/request/replacement-new.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/request-installations/new', name: 'app_admin_request_installation_new')]
    public function newInstallation(): Response
    {
        $breadcrumbs = $this->getBreadcrumbs('Proposition d\'installation');
        $breadcrumbs[1] = [
            'url' => $this->generateUrl('app_admin_request_installation'),
            'text' => 'Proposition d\'installation',
        ];
        $breadcrumbs[] = 'Nouvelle proposition d\'installation';
        return $this->render('admin/request/installation-new.html.twig', [
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/admin/request-replacements/detail/{id}', name: 'app_admin_request_replacement_show', requirements: ['id' => '\d+'])]
    public function showReplacement(int $id): Response
    {
        $request = $this->requestRepository->find($id);
        if (empty($request)) {
            throw new EntityNotFoundException('No entity found for #' . $id);
        }

        $breadcrumbs = $this->getBreadcrumbs('Demande de remplaçement');
        $breadcrumbs[1] = [
            'url' => $this->generateUrl('app_admin_request_replacement'),
            'text' => 'Demande de remplaçement',
        ];
        $breadcrumbs[] = sprintf('Demande de remplaçement #%d', $request->getId());
        return $this->render('admin/request/replacement-show.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'request' => $request,
        ]);
    }

    #[Route('/admin/request-installations/detail/{id}', name: 'app_admin_request_installation_show', requirements: ['id' => '\d+'])]
    public function showInstallation(int $id): Response
    {
        $request = $this->requestRepository->find($id);
        if (empty($request)) {
            throw new EntityNotFoundException('No entity found for #' . $id);
        }

        $breadcrumbs = $this->getBreadcrumbs('Proposition d\'installation');
        $breadcrumbs[1] = [
            'url' => $this->generateUrl('app_admin_request_installation'),
            'text' => 'Proposition d\'installation',
        ];
        $breadcrumbs[] = sprintf('Proposition d\'installation #%d', $request->getId());
        return $this->render('admin/request/installation-show.html.twig', [
            'breadcrumbs' => $breadcrumbs,
            'request' => $request,
        ]);
    }

    private function getBreadcrumbs(string $title)
    {
        return  [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            $title,
        ];
    }
}
