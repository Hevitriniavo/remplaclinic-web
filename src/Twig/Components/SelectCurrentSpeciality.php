<?php
namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectCurrentSpeciality
{
    public function getSpecialities(): array
    {
        return [
            [
                'id'  => 33,
                'name' => 'Assistant'
            ],
            [
                'id'  => 31,
                'name' => 'Chef de clinique'
            ],
            [
                'id'  => 34,
                'name' => 'Interne'
            ],
            [
                'id'  => 494,
                'name' => 'Médecin remplaçant'
            ],
            [
                'id'  => 32,
                'name' => 'Praticien hospitalier'
            ],
            [
                'id'  => 35,
                'name' => 'Autre'
            ]
        ];
    }
}