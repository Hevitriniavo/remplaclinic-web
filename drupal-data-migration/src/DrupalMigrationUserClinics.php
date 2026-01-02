<?php

namespace App\DrupalDataMigration;

class DrupalMigrationUserClinics extends DrupalMigrationBase
{
    public function migrate()
    {
        $userClinics = $this->getData([
            'q' => 'user_clinics',
        ]);

        // update field clinic_id of table user
        $this->connection->beginTransaction();
        try {
            foreach ($userClinics as $userClinic) {

                $this->connection->update(
                    'user',
                    [
                        'clinic_id' => $userClinic['entity_id'],
                    ],
                    [
                        'id' => $userClinic['field_clinique_uid']
                    ]
                );
            }

            $this->connection->commit();

            $this->log('info', count($userClinics) . ' user clinics imported.');

        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('error', $e->getMessage());

            throw $e;
        }
    }
}