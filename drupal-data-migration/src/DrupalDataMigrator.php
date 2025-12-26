<?php

namespace App\DrupalDataMigration;

use Doctrine\DBAL\Connection;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DrupalDataMigrator
{
    const MIGRATIONS = [
        'roles' => DrupalMigrationRoles::class,
        'specialities' => DrupalMigrationSpecialities::class,
        'regions' => DrupalMigrationRegions::class,
        'logo_partenaires' => DrupalMigrationLogoPartenaires::class,
        'references' => DrupalMigrationReferences::class,
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $httpClient
    )
    {}

    public function migrate(string $entityName) {
        $migration = $this->getMigration($entityName);

        $migration->migrate();
    }


    private function getMigration(string $entityName): DrupalMigration
    {
        if (array_key_exists($entityName, self::MIGRATIONS)) {
            $migration = self::MIGRATIONS[$entityName];

            $options = [
                'connection' => $this->connection,
                'http' => $this->httpClient,
            ];

            return new $migration($options);
        }

        return new DrupalMigrationUnknown();
    }
}