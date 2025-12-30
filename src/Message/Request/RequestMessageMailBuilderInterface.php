<?php
namespace App\Message\Request;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\User;

interface RequestMessageMailBuilderInterface
{
    /**
     * Create an email obect 
     * 
     * @param string $eventName The name of the dispatched event
     * @param Request $request The request source of the event
     * @param User $user The user related to the request
     * @param array $options Additional options to build the email
     * 
     * @return MailLog
     */
    public function build(
        string $eventName,
        ?Request $request,
        ?User $user,
        array $options = []
    ): MailLog;
}