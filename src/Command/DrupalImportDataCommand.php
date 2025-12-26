<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:drupal-import-data',
    description: 'Import data from old site. This command is the script to import data.',
)]
class DrupalImportDataCommand extends Command
{
    const ENTITY_NAMES = [
        'roles',
        'specialities',
        'regions',
        'logo_partenaires',
        'references',
        'users',
        'requests'
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $httpClient,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('entity_name', InputArgument::REQUIRED, 'Le nom de l\'entite a importer. Les valeurs possibles sont: ' . implode(', ', self::ENTITY_NAMES))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entityName = $input->getArgument('entity_name');

        if (in_array($entityName, self::ENTITY_NAMES)) {
            
            $migrator = new \App\DrupalDataMigration\DrupalDataMigrator(
                $this->connection,
                $this->httpClient
            );

            $migrator->migrate($entityName);

            $io->success('L\'entite ' .  $entityName . ' a ete importe!');

            return Command::SUCCESS;
        }
        
        $io->error('L\'entite ' .  $entityName . ' est invalide!');

        return Command::FAILURE;
    }
}
