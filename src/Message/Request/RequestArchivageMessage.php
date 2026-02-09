<?php
namespace App\Message\Request;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class RequestArchivageMessage
{
    public function __construct(
    ) {}
}