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
}
