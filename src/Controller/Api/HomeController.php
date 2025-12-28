<?php

namespace App\Controller\Api;

use App\Service\Dashboard\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/api/dashboard', name: 'api_home_dashboard')]
    public function index(DashboardService $dashboardService): Response
    {
        return $this->json($dashboardService->getData());
    }
}
