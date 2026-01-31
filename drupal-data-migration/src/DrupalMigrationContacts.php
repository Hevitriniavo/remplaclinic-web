<?php

namespace App\DrupalDataMigration;

use Exception;

class DrupalMigrationContacts extends DrupalMigrationBase
{
    public function migrate()
    {
        $payload = [];

        $this->addExtraCriteria($payload, 'gt_id', 'gt_sid');
        $this->addExtraCriteria($payload, 'id', 'sid');

        $totalCount = $this->getData(array_merge(
            $payload,
            [
                'q' => 'contacts',
                'count' => true,
            ]
        ));

        $total = $this->keyData($totalCount, 'total_count', 0);

        $this->log('info', 'Nombre de contacts a importer: ' . $total);

        $limit = $this->getOption('limit', 20);
        $page = 1;

        // limiter le nombre a traiter par command
        $totalATraiter = $this->getOption('max_count', $total);
        $total = min($totalATraiter, $total);

        for($i = $this->getOption('offset', 0); $i <= $total; $i += $limit) {
            $this->log('info', sprintf('Page [%d] - Limit: %d, Offset: %d', $page++, $limit, $i));

            $this->importContacts($limit, $i, $payload);
        }
    }

    private function importContacts(int $limit, int $offset, array $params = [])
    {
        $payload = array_merge($params, [
            'q' => 'contacts',
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $contacts = $this->getData($payload);

        $premierContact = '';
        $dernierContact = '';

        // insert content into table contact
        $this->connection->beginTransaction();
        try {
            foreach ($contacts as $contact) {
                $contactId = (int) $this->keyData($contact, 'submission.sid');
                $contactTypeId = $this->keyData($contact, 'submission.nid');
                $userId = (int) $this->keyData($contact, 'submission.uid');

                // for error verification @see catch block
                $dernierContact = $contactId;

                if (empty($premierContact)) {
                    $premierContact = $contactId;
                }

                $contactData = array_merge(
                    $this->getContactData($contactTypeId, $this->keyData($contact, 'data', [])),
                    [
                        'id' => $contactId,
                        'contact_type' => $contactTypeId,
                        'remote_addr' => $this->keyData($contact, 'submission.remote_addr'),
                        'user_id' => $userId === 0 ? null : $userId,
                        'submitted_at' => date('Y-m-d H:i', $this->keyData($contact, 'submission.submitted')),
                    ]
                );

                $this->connection->insert('contact', $contactData);
            }

            $this->connection->commit();

            // Logging

            $this->log('info', sprintf('Contact du %s au %s', $premierContact, $dernierContact));

        } catch (\Exception $e) {
            $this->connection->rollBack();

            $this->log('error', 'Contact qui a echoue: ' . $dernierContact);
            $this->log('error', $e->getMessage());

            throw $e;
        }
    }

    private function getContactData($rawNid, array $data): array
    {
        $nid = $rawNid ? (int) $rawNid : 86;

        if ($nid === 98) {
            // CONTACT_DEFAULT
            return [
                'name' => $this->keyData($data, 'nom'),
                'surname' => $this->keyData($data, 'prenom'),
                'telephone' => $this->keyData($data, 'telephone'),
                'email' => $this->keyData($data, 'email'),
                'message' => $this->keyData($data, 'message'),
            ];
        }

        if ($nid === 992) {
            // CONTACT_ASSITANCE
            return [
                'object' => $this->keyData($data, 'objet'),
                'name' => $this->keyData($data, 'nom'),
                'surname' => $this->keyData($data, 'prenom'),
                'fonction' => $this->keyData($data, 'fonction'),
                'email' => $this->keyData($data, 'e_mail'),
                'message' => $this->keyData($data, 'commentaire'),
            ];
        }

        if ($nid === 1030) {
            // CONTACT_OUVERTURE_COMPTE
            return [
                'name' => $this->keyData($data, 'nom'),
                'surname' => $this->keyData($data, 'prenom'),
                'telephone' => $this->keyData($data, 'telephone'),
                'email' => $this->keyData($data, 'email'),
                'message' => $this->keyData($data, 'message'),
            ];
        }

        if ($nid === 1085) {
            // CONTACT_INSTAL_CLINIC
            return [
                'name' => $this->keyData($data, 'instal_nom'),
                'surname' => $this->keyData($data, 'instal_prenom'),
                'telephone' => $this->keyData($data, 'instal_telephone'),
                'email' => $this->keyData($data, 'instal_email'),
                'message' => $this->keyData($data, 'instal_message'),
            ];
        }

        // CONTACT_UNKNOWN: nid = 86 or other
        return [
            'name' => $this->keyData($data, 'name'),
            'email' => $this->keyData($data, 'email'),
            'message' => $this->keyData($data, 'message'),
        ];
    }
}