<?php

namespace App\Entity;

enum RequestType: string {
    case REPLACEMENT = 'replacement';
    case INSTALLATION = 'installation';
}