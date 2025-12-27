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
        'logo_partenaires', // copy logo file after this
        'references',
        'users',
        'user_clinics', // copy user cv after this
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
            ->addArgument('entity_name', InputArgument::REQUIRED, 'Le nom de l\'entite a importer. Les valeurs possibles sont: ' . implode(', ', self::ENTITY_NAMES) . '. La valeur peut etre multiple en separant par virgule (e.g: roles,specialities,regions,users)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entityName = $input->getArgument('entity_name');

        $entityNames = explode(',', $entityName);

        // check if all entity names are valid
        foreach($entityNames as $ename) {
            if (!in_array($ename, self::ENTITY_NAMES)) {
                $io->error('L\'entite ' .  $ename . ' est invalide!');

                return Command::FAILURE;
            }
        }

        $migrator = new \App\DrupalDataMigration\DrupalDataMigrator(
            $this->connection,
            $this->httpClient
        );

        foreach($entityNames as $ename) {
            $migrator->migrate($ename);

            $io->success('L\'entite ' .  $ename . ' a ete importe!');
        }

        return Command::SUCCESS;
    }
}
