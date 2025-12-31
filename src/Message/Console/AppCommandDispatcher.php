<?php
namespace App\Message\Console;

use Symfony\Component\Messenger\MessageBusInterface;

class AppCommandDispatcher
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    )
    {}

    /**
     * Execute the given command line into messenger
     */
    public function runCommad(string $cmd, array $options = [])
    {
        $this->messageBus->dispatch(new RunCommandMessage($cmd, $options));
    }
}