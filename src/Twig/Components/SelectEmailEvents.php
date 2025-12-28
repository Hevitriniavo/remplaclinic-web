<?php

namespace App\Twig\Components;

use App\Entity\EmailEvents;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectEmailEvents
{
    public function getEvents(): array
    {
        return EmailEvents::all();
    }
}
