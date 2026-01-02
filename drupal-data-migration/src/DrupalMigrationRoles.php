<?php

namespace App\DrupalDataMigration;

class DrupalMigrationRoles extends DrupalMigrationBase
{
    public function migrate()
    {
        $roles = $this->getData(['q' => 'roles']);

        // insert content into table user_role
        $this->connection->beginTransaction();
        try {
            foreach ($roles as $role) {
                $this->connection->insert('user_role', [
                    'id' => $role['rid'],
                    'role' => $role['name']
                ]);
            }

            $this->connection->commit();

            $this->log('info', count($roles) . ' roles imported.');
        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('error', $e->getMessage());

            throw $e;
        }
    }
}