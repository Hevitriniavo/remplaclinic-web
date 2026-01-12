<?php

namespace App\Controller;

use App\Repository\RegionRepository;
use App\Security\SecurityUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonCompteController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
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
    public function mesDemandesRemplacements(): RedirectResponse
    {
        // switch($roles){
        // 	case 4:
        // 		echo $url.'/user/remplacants/mes-demandes-de-remplacements';
        // 		break;
        // 	case 5:
        // 		echo $url.'/user/cliniques/mes-demandes-de-remplacements';
        // 		break;
        // 	case 6:
        // 		echo $url.'/user/cliniques/mes-demandes-de-remplacements';
        // 		break;
        // 	case 7:
        // 		echo $url.'/user/directeur/mes-demandes-de-remplacements';
        // 		break;
        // }
        // @TODO: handle mes requests replacements here and fix redirect
        return $this->redirectToRoute('app_home');
    }

    #[Route(
        '/mon-compte/mes-propositions-d-installation',
        name: 'app_user_requets_installation'
    )]
    public function mesPropositionsInstallations(): RedirectResponse
    {
        // switch($roles){
        //     case 4:
        //         echo $url.'/user/remplacants/mes-propositions-d-installation';
        //         break;
        //     case 5:
        //         echo $url.'/user/cliniques/mes-propositions-d-installation';
        //         break;
        //     case 6:
        //         echo $url.'/user/cliniques/mes-propositions-d-installation';
        //         break;
        //     case 7:
        //         echo $url.'/user/directeur/mes-propositions-d-installation';
        //         break;
        // }
        // @TODO: handle mes requests replacements here and fix redirect
        return $this->redirectToRoute('app_home');
    }
}
