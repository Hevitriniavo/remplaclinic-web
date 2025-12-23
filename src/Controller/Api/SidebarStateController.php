<?php
namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SidebarStateController extends AbstractController
{
    #[Route('/api/sidebar-state/save', name: 'app_sidebar_state_save', methods: ['PUT'])]
    public function save(Request $request): Response
    {
        $session = $request->getSession();
        $collapsed = $request->request->getBoolean('collapsed');

        $session->set('sidebar_collapsed', $collapsed);

        return new JsonResponse('ok');
    }
}