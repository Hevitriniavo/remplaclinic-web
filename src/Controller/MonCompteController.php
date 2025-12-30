<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class MonCompteController extends AbstractController
{
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