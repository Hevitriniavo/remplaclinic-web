<?php

namespace App\Entity;

enum ResetPasswordStatus: string {
    case CREATED = 'created';
    case EXPIRED = 'expired';
    case USED = 'used';
}