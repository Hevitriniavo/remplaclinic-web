<?php

namespace App\Entity;

enum RequestReplacementType: string {
    case PONCTUAL = 'ponctual';
    case REGULAR = 'regular';
}