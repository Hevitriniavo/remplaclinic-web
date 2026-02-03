<?php

namespace App\DrupalDataMigration;

class DrupalMigrationUserClinics extends DrupalMigrationBase
{
    public function migrate()
    {
        $userClinics = $this->getData([
            'q' => 'user_clinics',
        ]);

        $lastInsertRow = [];

        // insert data to user_user table
        $this->connection->beginTransaction();
        try {
            foreach ($userClinics as $userClinic) {
                $lastInsertRow = $userClinic;

                $found = $this->connection->executeQuery('select count(*) as total from user_user where user_source = ? and user_target = ?', [$userClinic['entity_id'], $userClinic['field_clinique_uid']])
                    ->fetchNumeric();
                
                if ($found[0] === 0) {
                    $this->connection->insert(
                        'user_user',
                        [
                            'user_source' => $userClinic['entity_id'],
                            'user_target' => $userClinic['field_clinique_uid'],
                        ]
                    );
                }

            }

            $this->connection->commit();

            $this->log('info', count($userClinics) . ' user clinics imported.');

        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('info', sprintf('Last inserted data: user_source = %d, user_target = %d', $lastInsertRow['entity_id'], $lastInsertRow['field_clinique_uid']));
            $this->log('error', $e->getMessage());

            throw $e;
        }
    }
}