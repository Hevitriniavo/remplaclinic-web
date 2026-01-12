<?php
namespace App\Twig\Components;

use App\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectCurrentSpeciality
{
    public function getSpecialities(): array
    {
        $result = [];
        foreach(User::allCurrentSpecialities() as $id => $currentSpeciality) {
            $result[] = [
                'id' => $id,
                'name' => $currentSpeciality
            ];
        }
        return $result;
    }
}