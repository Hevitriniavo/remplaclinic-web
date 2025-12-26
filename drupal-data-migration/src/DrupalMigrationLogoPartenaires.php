<?php

namespace App\DrupalDataMigration;

class DrupalMigrationLogoPartenaires extends DrupalMigrationBase
{
    public function migrate()
    {
        $logoPartenaires = $this->getData(['q' => 'partenaires']);

        // insert content into table partenaire_logo
        $this->connection->beginTransaction();
        try {
            foreach ($logoPartenaires as $logoPartenaire) {
                $this->connection->insert('partenaire_logo', [
                    'id' => $logoPartenaire['nid'],
                    'name' => $logoPartenaire['title'],
                    'logo' => $logoPartenaire['field_logo']['und'][0]['filename'],
                ]);
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}