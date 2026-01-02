<?php

namespace App\DrupalDataMigration;

class DrupalMigrationSpecialities extends DrupalMigrationBase
{
    public function migrate()
    {
        $specialities = $this->getData([
            'q' => 'taxonomies',
            'vocabulary' => 'sp_cialit_',
        ]);

        // insert content into table speciality
        $this->connection->beginTransaction();
        try {
            foreach ($specialities as $speciality) {
                $specialityData = [
                    'id' => $speciality['tid'],
                    'name' => $speciality['name']
                ];

                $parentId = $speciality['parents'];

                if (!empty($parentId) && intval($parentId[0]) > 0) {
                    $specialityData['speciality_parent_id'] = intval($parentId[0]);
                }

                $this->connection->insert('speciality', $specialityData);
            }

            $this->connection->commit();

            $this->log('info', count($specialities) . ' specialities imported.');

        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('error', $e->getMessage());

            throw $e;
        }
    }
}