<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
        'requests',
        'request_responses'
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
            ->addOption('base_uri', 'uri', InputOption::VALUE_OPTIONAL, 'L\URL du site drupal a importer.')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Le nombre d\'element a traiter par page.')
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Le nombre d\'element a ignorer avant le debut du traitement.')
            ->addOption('id', 'id', InputOption::VALUE_OPTIONAL, 'Pour traiter un seul element par son id.')
            ->addOption('gt_id', 'gi', InputOption::VALUE_OPTIONAL, 'Pour limiter le traitement aux elements dont son id est superieur a l\'id fourni.')
            ->addOption('max_count', 'mc', InputOption::VALUE_OPTIONAL, 'Le nombre maximale d\'element a traiter. Necessaire pour ne pas traiter en une seule fois un gros volume de donnee (e.g: candidature).')
            ->addOption('app_importation_id', 'app', InputOption::VALUE_OPTIONAL, 'L\'ID du script d\'imortation.')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Pour filtrer les elements par son type. Type disponible: requests (demande_de_remplacement, demande_d_installation), request_response (candidature_installation, candidature_remplacement).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityName = $input->getArgument('entity_name');

        $entityNames = explode(',', $entityName);

        // check if all entity names are valid
        foreach($entityNames as $ename) {
            if (!in_array($ename, self::ENTITY_NAMES)) {
                $output->writeln('<error>L\'entite ' .  $ename . ' est invalide!</error>');

                return Command::FAILURE;
            }
        }

        $migrator = new \App\DrupalDataMigration\DrupalDataMigrator(
            $this->connection,
            $this->httpClient,
            $input->getOptions(),
            $this->getMigratorEventHandlers($output)
        );

        foreach($entityNames as $ename) {
            $migrator->migrate($ename);

            $output->writeln('<info>L\'entite ' .  $ename . ' a ete importe!</info>');
        }

        return Command::SUCCESS;
    }

    private function getMigratorEventHandlers(OutputInterface $output): ?\App\DrupalDataMigration\DrupalMigrationEventHandlerInterface
    {
        return new class($output) implements \App\DrupalDataMigration\DrupalMigrationEventHandlerInterface {
            private readonly OutputInterface $output;

            public function __construct(OutputInterface $output)
            {
                $this->output = $output;
            }

            public function handleEvent(?string $eventName, array $options = [])
            {
                $this->output->writeln($eventName);
            }
        };
    }
}
