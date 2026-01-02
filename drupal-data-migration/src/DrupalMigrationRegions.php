<?php

namespace App\DrupalDataMigration;

class DrupalMigrationRegions extends DrupalMigrationBase
{
    public function migrate()
    {
        $regions = $this->getData([
            'q' => 'taxonomies',
            'vocabulary' => 'r_gion',
        ]);

        // insert content into table region
        $this->connection->beginTransaction();
        try {
            foreach ($regions as $region) {

                $this->connection->insert('region', [
                    'id' => $region['tid'],
                    'name' => $region['name']
                ]);
            }

            $this->connection->commit();

            $this->log('info', count($regions) . ' regions imported.');

        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('error', $e->getMessage());

            throw $e;
        }
    }
}