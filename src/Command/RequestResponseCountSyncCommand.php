<?php

namespace App\Command;

use App\Service\Request\RequestResponseCountSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:request-response-count-sync',
    description: 'Update column responseCount for all request',
)]
class RequestResponseCountSyncCommand extends Command
{
    public function __construct(
        private readonly RequestResponseCountSynchronizer $synchronizer
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $affectedRows = $this->synchronizer->updateAllRequests();

        $io->success(sprintf('Nombre de ligne mise a jour: %d', $affectedRows));

        return Command::SUCCESS;
    }
}
