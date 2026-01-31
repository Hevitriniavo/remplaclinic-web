<?php

namespace App\Entity;

enum ContactObject: string {
    case ASSISTANCE_COMPTABLE = 'assistance_comptable';
    case ASSISTANCE_JURIDIQUE = 'assistance_juridique';
    case ASSISTANCE_PROFESSIONNELLE = 'assurance_professionnelle';
}