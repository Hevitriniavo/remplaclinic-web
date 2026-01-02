<?php

namespace App\DrupalDataMigration;

class DrupalMigrationReferences extends DrupalMigrationBase
{
    public function migrate()
    {
        $references = $this->getData(['q' => 'temoignages']);

        // insert content into table evidence
        $this->connection->beginTransaction();
        try {
            foreach ($references as $reference) {
                $referenceData = [
                    'id' => $reference['nid'],
                    'title' => $reference['title'],
                    'body' => $reference['body']['fr'][0]['safe_value'],
                ];

                $clinic = $this->keyData($reference, 'field_nom_clinique.und.0.safe_value');
                if (!empty($clinic)) {
                    $referenceData['clinic_name'] = $clinic;
                }

                $statut = $this->keyData($reference, 'field_statut_temoignage.und.0.safe_value');
                if (!empty($statut)) {
                    $referenceData['speciality_name'] = $statut;
                }

                $this->connection->insert('evidence', $referenceData);
            }

            $this->connection->commit();

            $this->log('info', count($references) . ' references imported.');

        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('error', $e->getMessage());

            throw $e;
        }
    }
}