<?php

namespace App\DrupalDataMigration;

interface DrupalMigrationEventHandlerInterface
{
    public function handleEvent(?string $eventName, array $options = []);
}