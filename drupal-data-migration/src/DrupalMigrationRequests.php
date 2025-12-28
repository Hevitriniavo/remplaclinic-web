<?php

namespace App\DrupalDataMigration;

class DrupalMigrationRequests extends DrupalMigrationBase
{
    public function migrate()
    {
        $payload = [
            // 'gt_nid' => 1332,
            // 'nid' => 837,
        ];

        $totalCount = $this->getData(array_merge(
            $payload,
            [
                'q' => 'requests',
                'count' => true,
            ]
        ));

        $total = $this->keyData($totalCount, 'total_count', 0);

        echo 'Nombre de demande a importer: ' . $total . PHP_EOL;

        $limit = 20;
        $page = 1;
        for($i = 0; $i <= $total; $i += $limit) {
            echo sprintf('Page [%d] - Limit: %d, Offset: %d', $page++, $limit, $i). PHP_EOL;

            $this->importRequests($limit, $i, $payload);
        }
    }

    private function importRequests(int $limit, int $offset, array $params = [])
    {
        $payload = array_merge($params, [
            'q' => 'requests',
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $requests = $this->getData($payload);

        $premierRequest = '';
        $dernierRequest = '';
        $invalidRequest = [];
        $validRequest = [];

        // insert content into table request, request_history, request_reason, request_response, request_speciality
        $this->connection->beginTransaction();
        try {
            foreach ($requests as $request) {
                // for error verification @see catch block
                $dernierRequest = $request['nid'];

                if ($this->isRequestValid($request)) {
                    if (empty($premierRequest)) {
                        $premierRequest = $request['nid'];
                    }

                    $applicantId = $this->storeApplicant($request);
                    $regionId = $this->storeRegion($request);
                    $specialityId = $this->storeSpeciality($request);

                    $requestData = [
                        'id' => $request['nid'],
                        'applicant_id' => $applicantId,
                        'region_id' => $regionId,
                        'speciality_id' => $specialityId,
                        'title' => $request['title'],
                        'status' => $this->getStatut($request),
                        'started_at' => $this->keyData($request, 'field_date_demande.und.0.value'),
                        // @TODO: check those fields on prod -> they can't change in local env
                        // 'show_end_at' => ,
                        // 'end_at' => ,
                        'last_sent_at' => $this->getLastSentDate($request),
                        'request_type' => $this->getRequestType($request),
                        'remuneration' => $this->getRemuneration($request),
                        'comment' => $this->getComment($request),
                        'position_count' => $this->keyData($request, 'field_nombre_de_postes.und.0.value'),
                        'accomodation_included' => $this->getLogementInclusPossible($request),
                        'transport_cost_refunded' => $this->getTransportInclus($request),
                        'retrocession' => $this->keyData($request, 'field_r_trocession.und.0.safe_value'),
                        'replacement_type' => $this->getRemplacementType($request),
                        'created_at' => date('Y-m-d H:i', $request['created']),
                    ];

                    $this->connection->insert('request', $requestData);

                    $this->storeHistories($request);
                    $this->storeSubSpecialities($request);
                    $this->storeRaisons($request);

                    // Do this step independantly because it consume more memory
                    // $this->storeResponses($request);

                    array_push($validRequest, $request['nid']);
                } else {
                    array_push($invalidRequest, $request['nid']);
                }
            }

            $this->connection->commit();

            // Logging

            echo sprintf('Demande du %s au %s', $premierRequest, $dernierRequest). PHP_EOL;
            echo sprintf('Demande valide: %s (%d)', implode(', ', $validRequest), count($validRequest)). PHP_EOL;
            echo sprintf('Demande invalide: %s (%d)', implode(', ', $invalidRequest), count($invalidRequest)). PHP_EOL;

        } catch (\Exception $e) {
            $this->connection->rollBack();

            echo 'Demande qui a echoue: ' . $dernierRequest . PHP_EOL;

            throw $e;
        }
    }

    private function isRequestValid(array $request): bool
    {
        $estTypeValide = $request['type'] === 'demande_de_remplacement' || $request['type'] === 'demande_d_installation';

        $estUserValide = !empty($request['uid']);

        return $estTypeValide && $estUserValide;
    }

    private function getRequestType(array $request): string
    {
        $typeMap = [
            'demande_de_remplacement' => 'replacement', // demande de remplacement
            'demande_d_installation' => 'installation', // demande d'installation
        ];

        return $typeMap[$request['type']];
    }
    
    private function getRemuneration(array $request): ?string
    {
        $fieldName = $request['type'] === 'demande_de_remplacement' ? 'field_r_mun_ration' : 'field_salaire';

        return $this->keyData($request, $fieldName . '.und.0.safe_value');
    }

    private function storeApplicant(array $request): ?int
    {
        if (empty($request['uid'])) {
            return null;
        }
        
        return intval($request['uid']);
    }

    private function storeRegion(array $request) : ?int
    {
        $regionId = $this->keyData($request, 'field_region.und.0.tid');
        
        return $regionId === null ? null : intval($regionId);
    }

    private function storeSpeciality(array $request) : ?int
    {
        $specialityId = $this->keyData($request, 'field_sp_cialit_.und.0.tid');
        
        return $specialityId === null ? null : intval($specialityId);
    }

    private function getComment($request): ?string
    {
        $commentaireValue = $this->keyData($request, 'field_commentaire.und.0');

        if (empty($commentaireValue)) {
            return null;
        }

        if (!empty($commentaireValue['safe_value'])) {
            return $commentaireValue['safe_value'];
        }

        if (!empty($commentaireValue['value'])) {
            return $commentaireValue['value'];
        }

        return null;
    }

    private function getStatut($request): ?int
    {
        $statutId = $this->keyData($request, 'field_statut.und.0.tid', '38');

        $statutMap = [
            '36' => 1, // En cours
            '37' => 0, // A valider
            '38' => 2, // Archive
        ];

        return array_key_exists($statutId, $statutMap) ? $statutMap[$statutId] : 2;
    }

    private function getLogementInclusPossible($request): ?int
    {
        $logementValue = $this->keyData($request, 'field_logement_inclus_possible.und.0.value');

        if (empty($logementValue)) {
            return null;
        }

        $logementMap = [
            '0' => 0, // Non
            '1' => 1, // Oui
            '2' => 2, // A debattre
        ];

        return array_key_exists($logementValue, $logementMap) ? $logementMap[$logementValue] : null;
    }

    private function getTransportInclus($request): ?int
    {
        $transportValue = $this->keyData($request, 'field_remboursement_frais_de_tra.und.0.value');

        if (empty($transportValue)) {
            return null;
        }

        $transportMap = [
            '0' => 0, // Non
            '1' => 1, // Oui
            '2' => 2, // A debattre
        ];

        return array_key_exists($transportValue, $transportMap) ? $transportMap[$transportValue] : null;
    }

    private function getRemplacementType($request): ?string
    {
        $remplacementValue = $this->keyData($request, 'field_remplacement.und.0.value');

        if (empty($remplacementValue)) {
            return null;
        }

        $remplacementMap = [
            '0' => 'ponctual', // Ponctuel
            '1' => 'regular', // Regulier
        ];

        return array_key_exists($remplacementValue, $remplacementMap) ? $remplacementMap[$remplacementValue] : null;
    }

    private function getLastSentDate(array $request): ?string
    {
        $histories = $this->keyData($request, 'field_date_des_envois.und');

        if (!empty($histories)) {
            return $histories[count($histories) - 1]['value'];
        }

        return null;
    }

    private function storeHistories(array $request)
    {
        $histories = $this->keyData($request, 'field_date_des_envois.und');

        if (!empty($histories)) {
            foreach($histories as $dateEnvoi) {
                $this->connection->insert('request_history', [
                    'request_id' => $request['nid'],
                    'sent_at' => $dateEnvoi['value']
                ]);
            }
        }
    }

    private function storeRaisons(array $request)
    {
        $raisons = $this->keyData($request, 'field_raison.und');

        if (!empty($raisons)) {
            $raisonMap = [
                '0' => "Recrutement afin de compléter l'équipe actuelle",
                '1' => "Création de poste",
                '2' => "Création de cabinet ou proposition d'association",
                '3' => "Cession de patientèle ou de fonds de commerce",
                '4' => "Cession de parts d'une société (SCM, SELARL, etc)",
                '5' => "Départ en retraite",
                '6' => "Autre",
            ];

            foreach($raisons as $raison) {

                $raisonData = 'Autre';
                $raisonValue = null;

                if (!is_null($raison['safe_value']) && $raison['safe_value'] != '') {
                    if (array_key_exists($raison['safe_value'], $raisonMap)) {
                        $raisonData = $raisonMap[$raison['safe_value']];
                    } else {
                        if (in_array($raison['safe_value'], array_values($raisonMap))) {
                            $raisonData = $raison['safe_value'];
                        } else {
                            $raisonValue = $raison['safe_value'];
                        }
                    }
                }

                $this->connection->insert('request_reason', [
                    'request_id' => $request['nid'],
                    'reason' => $raisonData,
                    // @TODO: check this field on prod -> it can't change in local env
                    'reason_value' => $raisonValue
                ]);
            }
        }
    }

    private function storeSubSpecialities(array $request)
    {
        $specialities = $this->keyData($request, 'field_sous_sp_cialit_.und');
        
        if (!empty($specialities)) {
            $specialitiesId = array_map(function ($row) { return +$row['tid']; }, $specialities);

            // remove duplicate
            $specialitiesId = array_unique($specialitiesId, SORT_NUMERIC);

            sort($specialitiesId);

            foreach($specialitiesId as $speciality) {
                $this->connection->insert('request_speciality', [
                    'request_id' => $request['nid'],
                    'speciality_id' => $speciality,
                ]);
            }
        }
    }

    // private function storeResponses(array $request)
    // {
    //     $reponses = $this->keyData($request, 'candidatures', []);

    //     if (!empty($reponses)) {
    //         foreach($reponses as $reponse) {
    //             $this->connection->insert('request_response', [
    //                 'request_id' => $request['nid'],
    //                 'user_id' => $reponse['uid'],
    //                 'status' => $reponse['statut'],
    //                 'create_at' => date('Y-m-d H:i'),
    //                 'updated_at' => date('Y-m-d H:i'),
    //             ]);
    //         }
    //     }
    // }
}