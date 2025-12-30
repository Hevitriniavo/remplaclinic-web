<?php
namespace App\Message\Request;

use App\Entity\MailLog;

interface RequestMessageMailSenderInterface
{
    public function send(MailLog $mailLog): ?MailLog;
}