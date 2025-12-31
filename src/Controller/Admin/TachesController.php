<?php

namespace App\Controller\Admin;

use App\Service\Taches\AppConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TachesController extends AbstractController
{
    #[Route('/admin/taches/configurations', name: 'app_admin_taches_list')]
    public function getAppConfigurations(AppConfigurationService $appConfigurationService): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Configurations',
        ];

        $viewData = [
            'breadcrumbs' => $breadcrumbs,
            'requiredConfigurations' => implode(',', AppConfigurationService::REQUIRED),
            'missingConfigurations' => implode(',', $appConfigurationService->checkRequiredValues())
        ];

        return $this->render('admin/taches/app-config.html.twig', $viewData);
    }

    #[Route('/admin/taches/evenements', name: 'app_admin_taches_messages')]
    public function getAllMessages(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Messages',
        ];

        return $this->render('admin/taches/messengers.html.twig', [
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    #[Route('/admin/taches/importations', name: 'app_admin_taches_importations')]
    public function getAllImportations(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Importations',
        ];

        return $this->render('admin/taches/importations.html.twig', [
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    #[Route('/admin/taches/schedulers', name: 'app_admin_taches_schedulers')]
    public function getAllSchedulers(): Response
    {
        $breadcrumbs = [
            [
                'url' => $this->generateUrl('app_admin_home'),
                'text' => 'Home',
            ],
            'Schedulers',
        ];

        return $this->render('admin/taches/schedulers.html.twig', [
            'breadcrumbs' => $breadcrumbs
        ]);
    }
}
