<?php
namespace App\Message\User;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class UserAbonnementMessage
{
    public function __construct(
    ) {}
}