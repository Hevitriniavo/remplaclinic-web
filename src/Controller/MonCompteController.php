<?php

namespace App\Controller;

use App\Entity\Request;
use App\Entity\RequestType;
use App\Pagination\Pagination;
use App\Repository\RegionRepository;
use App\Repository\RequestRepository;
use App\Security\SecurityUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MonCompteController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestRepository $requestRepository,
    ) {}

    #[Route(
        '/mon-compte/espace-perso',
        name: 'app_user_espace_perso'
    )]
    public function monCompte(): Response
    {
        $accountLinks = [
            [
                'icon' => 'icone-profil.png',
                'url' => $this->generateUrl('app_user_infos'),
                'text' => 'Mes Informations Personnelles',
            ],
            [
                'icon' => 'icone-demandes.png',
                'url' => $this->generateUrl('app_user_requets_replacement'),
                'text' => 'Mes demandes de remplacement',
            ],
            [
                'icon' => 'icon_mes_propositions.png',
                'url' => $this->generateUrl('app_user_requets_installation'),
                'text' => "Mes propositions d'installation",
            ],
        ];

        if ($this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_CLINIC')) {
            array_push(
                $accountLinks,
                [
                    'icon' => 'nouvelle-demande-remplacement.png',
                    'url' => $this->generateUrl('app_user_requets_replacement_new'),
                    'text' => 'Effectuer une nouvelle demande de remplacement',
                ],
                [
                    'icon' => 'nouvelle-demande-remplacement.png',
                    'url' => $this->generateUrl('app_user_requets_installation_new'),
                    'text' => "Effectuer une nouvelle proposition d'installation",
                ]
            );
        }

        return $this->render('espace-perso/index.html.twig', [
            'accountLinks' => $accountLinks,
        ]);
    }

    #[Route(
        '/mon-compte/mes-infos-personnelles',
        name: 'app_user_infos'
    )]
    public function mesInfosPersonnelles(RegionRepository $regionRepository): Response
    {
        /**
         * @var SecurityUser
         */
        $connectedUser = $this->security->getUser();

        $viewName = 'new_replacement';
        $viewData = [
            'regions' => [],
            'connected' => true,
            'connectedUser' => $connectedUser->getUser()
        ];

        if ($this->security->isGranted('ROLE_REPLACEMENT')) {
            $viewData['regions'] =  $regionRepository->findAll();
        } else if ($this->security->isGranted('ROLE_CLINIC')) {
            $viewName = 'new_clinic';
        } else if ($this->security->isGranted('ROLE_DOCTOR')) {
            $viewName = 'new_doctor';
        }

        return $this->render('register/' . $viewName . '.html.twig', $viewData);
    }

    #[Route(
        '/mon-compte/mes-demandes-de-remplacements/new',
        name: 'app_user_requets_replacement_new'
    )]
    public function newMesDemandesRemplacements(): Response
    {
        return $this->render('espace-perso/mes-demandes/remplacement-new.html.twig');
    }

    #[Route(
        '/mon-compte/mes-propositions-d-installation/new',
        name: 'app_user_requets_installation_new'
    )]
    public function newMesPropositionsInstallations(): Response
    {
        return $this->render('espace-perso/mes-demandes/installation-new.html.twig');
    }

    #[Route(
        '/mon-compte/mes-demandes-de-remplacements',
        name: 'app_user_requets_replacement'
    )]
    public function mesDemandesRemplacements(HttpFoundationRequest $request): Response
    {
        return $this->renderViewForUser($request, RequestType::REPLACEMENT);
    }

    #[Route(
        '/mon-compte/mes-propositions-d-installation',
        name: 'app_user_requets_installation'
    )]
    public function mesPropositionsInstallations(HttpFoundationRequest $request): Response
    {
        return $this->renderViewForUser($request, RequestType::INSTALLATION);
    }

    private function renderViewForUser(HttpFoundationRequest $request, RequestType $requestType): Response
    {
        /**
         * @var SecurityUser
         */
        $user = $this->security->getUser();

        $viewPath = '';
        $templateName = $requestType === RequestType::INSTALLATION ? 'mes-propositions' : 'mes-demandes';
        $routeName = $requestType === RequestType::INSTALLATION ? 'app_user_requets_installation' : 'app_user_requets_replacement';
        $viewData = [
            'requests' => [],
            'params' => [],
        ];

        $pagination = new Pagination($request->query->all());
        $status = $request->query->get('status');
        $status = in_array($status, [Request::CREATED, Request::ARCHIVED, Request::IN_PROGRESS]) ? $status : null;

        if ($this->security->isGranted('ROLE_CLINIC') || $this->security->isGranted('ROLE_DOCTOR')) {
            $viewPath = 'clinique-cabinet';

            $viewData['requests'] = $this->requestRepository->findAllByUserId(
                $user->getUser()->getId(),
                $requestType,
                $pagination->limit,
                $pagination->offset,
                $status
            );

            $routeParams = is_null($status) ? [] : ['status' => (int) $status];
            $routeParams['limit'] = $pagination->limit;

            $viewData['params'] = array_merge($routeParams, $pagination->toArray(), [
                '_url' => $this->generateUrl($routeName, $routeParams)
            ]);

        } else if ($this->security->isGranted('ROLE_REPLACEMENT')) {
            $viewPath = 'remplacant';
        } else {
            throw new AccessDeniedException('Access denied.');
        }

        return $this->render('espace-perso/mes-demandes/' . $viewPath . '/' . $templateName . '.html.twig', $viewData);
    }
}
