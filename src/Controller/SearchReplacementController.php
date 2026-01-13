<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchReplacementController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    )
    {}

    #[Route('/rechercher-remplacants', name: 'app_search_replacement')]
    public function index (Request $request): Response
    {
        $params = $request->query->all();
        $routeParams = [];

        // region
        if (!empty($params['region'])) {
            if (strtolower($params['region']) !== 'all') {
                $params['region_id'] = (int) $params['region'];
                $routeParams['region'] = $params['region'];
            }
            unset($params['region']);
        }

        // specialite
        if (!empty($params['specialite'])) {
            if (strtolower($params['specialite']) !== 'all') {
                $params['speciality_id'] = (int) $params['specialite'];
                $routeParams['specialite'] = $params['specialite'];
            }
            unset($params['specialite']);
        }

        // limit
        if (!array_key_exists('limit', $params)) {
            $params['limit'] = 20;
        } else {
            $params['limit'] = (int) $params['limit'];
        }
        $routeParams['limit'] = $params['limit'];

        // page
        if (array_key_exists('page', $params)) {
            $page = (int) $params['page'];

            $params['offset'] = ($page - 1) * $params['limit'];

            unset($params['page']);
        }

        // offset
        if (!array_key_exists('offset', $params)) {
            $params['offset'] = 0;
        } else {
            $params['offset'] = (int) $params['offset'];
        }

        // role
        $params['role_id'] = User::ROLE_REPLACEMENT_ID;

        return $this->render('search_replacement/index.html.twig', [
            'result' => $this->userRepository->findAllByParams($params),
            'params' => array_merge($params, [
                '_url' => $this->generateUrl('app_search_replacement', $routeParams)
            ]),
        ]);
    }
}
