<?php

namespace App\Controller;

use App\Controller\Trait\SearchReplacementTrait;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchReplacementController extends AbstractController
{
    use SearchReplacementTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
    )
    {}

    #[Route('/rechercher-remplacants', name: 'app_search_replacement')]
    public function index (Request $request): Response
    {
        $params = $this->getSearchParams($request);

        $routeParams = $params['extra_options'];
        $routeParams['limit'] = $params['limit'];

        return $this->render('search_replacement/index.html.twig', [
            'result' => $this->userRepository->findAllByParams($params),
            'params' => array_merge($params, [
                '_url' => $this->generateUrl('app_search_replacement', $routeParams)
            ]),
        ]);
    }
}
