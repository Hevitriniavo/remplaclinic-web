<?php

namespace App\DrupalDataMigration;

class DrupalMigrationRequestResponses extends DrupalMigrationBase
{
    public function migrate()
    {
        // $this->migrateRequestCandidaturesFor('candidature_installation');
        // $this->migrateRequestCandidaturesFor('candidature_remplacement');
        $this->migrateRequestCandidaturesFor($this->getOption('type'));
    }

    private function migrateRequestCandidaturesFor(string $type)
    {
        $payload = [
            'type' => $type,
        ];

        $this->addExtraCriteria($payload, 'gt_id', 'gt_id');
        $this->addExtraCriteria($payload, 'id', 'id');

        $totalCount = $this->getData(array_merge(
            $payload,
            [
                'q' => 'request_responses',
                'count' => true,
            ]
        ));

        $total = $this->keyData($totalCount, 'total_count', 0);

        echo 'Nombre de candidature a importer: ' . $total . PHP_EOL;

        $limit = $this->getOption('limit', 1000);
        $page = 1;

        // limiter le nombre a traiter par command
        $totalATraiter = $this->getOption('max_count', $total);
        $total = min($totalATraiter, $total);

        for($i = $this->getOption('offset', 0); $i <= $total; $i += $limit) {
            echo sprintf('Page [%d] - Limit: %d, Offset: %d', $page++, $limit, $i). PHP_EOL;

            $this->importRequestCandidatures($limit, $i, $payload);
        }
    }

    private function importRequestCandidatures(int $limit, int $offset, array $params = [])
    {
        $payload = array_merge($params, [
            'q' => 'request_responses',
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $httpResponse = $this->getData($payload);
        $candidatures = $this->keyData($httpResponse, 'candidatures', []);

        if (empty($candidatures)) {
            return;
        }

        $premierCandidature = '';
        $dernierCandidature = '';
        $invalidCandidature = [];
        $validCandidature = [];

        // insert content into table request_response
        $total = count($candidatures);
        for ($i = 0; $i < $total; ) {
            $this->connection->beginTransaction();
            try {
                
                $transactionLen = min($total, $i + 100);

                for($j = $i; $j < $transactionLen; $j++, $i++) {
                    $candidature = $candidatures[$j];

                    // for error verification @see catch block
                    $dernierCandidature = $candidature['id'];

                    if ($this->isResponseValid($candidature)) {
                        if (empty($premierCandidature)) {
                            $premierCandidature = $candidature['id'];
                        }

                        $this->connection->insert('request_response', [
                            'request_id' => $candidature['nid'],
                            'user_id' => $candidature['uid'],
                            'status' => $candidature['statut'],
                            'create_at' => date('Y-m-d H:i'),
                            'updated_at' => date('Y-m-d H:i'),
                        ]);

                        array_push($validCandidature, $candidature['id']);
                    } else {
                        array_push($invalidCandidature, $candidature['nid']);
                    }
                }

                $this->connection->commit();

            } catch (\Exception $e) {
                $this->connection->rollBack();
    
                echo 'Candidature qui a echoue: ' . $dernierCandidature . '('. $payload['type'] .')' . PHP_EOL;
    
                throw $e;
            }
        }

        // Logging
        echo sprintf('Candidature du %s au %s', $premierCandidature, $dernierCandidature). PHP_EOL;
        echo sprintf('Candidature valide: %d', count($validCandidature)). PHP_EOL;
        echo sprintf('Candidature invalide: %d', count($invalidCandidature)). PHP_EOL;
    }

    private function isResponseValid(array $request): bool
    {
        return true;
    }
}