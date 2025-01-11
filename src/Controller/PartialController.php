<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PartialController extends AbstractController
{

    public function index(): Response
    {
        $regions = [
            ['value' => 'All', 'label' => 'Région'],
            ['value' => '463', 'label' => 'Alsace'],
            ['value' => '464', 'label' => 'Aquitaine'],
        ];

        $specialites = [
            ['value' => 'All', 'label' => 'Spécialité'],
            ['value' => '287', 'label' => 'Anatomopathologie'],
            ['value' => '268', 'label' => 'Anesthésie-réanimation'],
        ];

        $partenaires = [
            ['src' => 'images/clinea_new-bleu-rvb_002.jpg', 'alt' => 'Logo Clinea'],
            ['src' => 'images/ramsay_logo.jpg', 'alt' => 'Logo Ramsay'],
            ['src' => 'images/elsan_logo.png', 'alt' => 'Logo Elsan'],
            ['src' => 'images/logo-inicea.jpg', 'alt' => 'Logo Inicea'],
            ['src' => 'images/logo_c2s_.png', 'alt' => 'Logo C2S'],
            ['src' => 'images/macsf.png', 'alt' => 'Logo MACSF'],
        ];

        return $this->render('partials/search_by_country.html.twig', [
            'regions' => $regions,
            'specialites' => $specialites,
            'partenaires' => $partenaires,
            'nombreRemplacants' => 7873,
        ]);
    }
}
