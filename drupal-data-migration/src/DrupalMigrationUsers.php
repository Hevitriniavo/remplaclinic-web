<?php

namespace App\DrupalDataMigration;

class DrupalMigrationUsers extends DrupalMigrationBase
{
    public function migrate()
    {
        $payload = [];

        $this->addExtraCriteria($payload, 'gt_id', 'gt_uid');
        $this->addExtraCriteria($payload, 'id', 'uid');

        $totalCount = $this->getData(array_merge(
            $payload,
            [
                'q' => 'users',
                'count' => true,
            ]
        ));

        $total = $this->keyData($totalCount, 'total_count', 0);

        echo 'Nombre d\'utilisateur a importer: ' . $total . PHP_EOL;

        $limit = $this->getOption('limit', 20);
        $page = 1;

        // limiter le nombre a traiter par command
        $totalATraiter = $this->getOption('max_count', $total);
        $total = min($totalATraiter, $total);

        for($i = $this->getOption('offset', 0); $i <= $total; $i += $limit) {
            echo sprintf('Page [%d] - Limit: %d, Offset: %d', $page++, $limit, $i). PHP_EOL;

            $this->importUsers($limit, $i, $payload);
        }
    }

    private function importUsers(int $limit, int $offset, array $params = [])
    {
        $payload = array_merge($params, [
            'q' => 'users',
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $users = $this->getData($payload);

        $premierUser = '';
        $dernierUser = '';
        $invalidUser = [];
        $validUser = [];

        // insert content into table user, user_address, user_establishment, user_region, user_user_role, user_subscription, user_speciality
        $this->connection->beginTransaction();
        try {
            foreach ($users as $user) {
                // for error verification @see catch block
                $dernierUser = $user['uid'];

                if ($this->isUserValid($user)) {
                    if (empty($premierUser)) {
                        $premierUser = $user['uid'];
                    }

                    $addressId = $this->storeAddress($user);
                    $establishmentId = $this->storeEstablishment($user);
                    $specialityId = $this->storeSpeciality($user);
                    $subscriptionId = $this->storeSubscription($user);

                    $userData = [
                        'id' => $user['uid'],
                        'address_id' => $addressId,
                        'speciality_id' => $specialityId,
                        'subscription_id' => $subscriptionId,
                        'establishment_id' => $establishmentId,
                        'clinic_id' => null,
                        'ordinary_number' => $this->truncValue($this->keyData($user, 'field_num_ordre.und.0.safe_value')),
                        'civility' => $this->keyData($user, 'field_civilit_.und.0.value'),
                        'surname' => $this->keyData($user, 'field_prenom.und.0.safe_value'),
                        'name' => $this->keyData($user, 'field_nom.und.0.safe_value'),
                        'year_of_birth' => $this->keyData($user, 'field_ann_e_de_naissance.und.0.safe_value'),
                        'nationality' => $this->keyData($user, 'field_nationalite.und.0.safe_value'),
                        'email' => $user['mail'],
                        'password' => $user['pass'],
                        'status' => $user['status'],
                        'telephone' => $this->keyData($user, 'field_telephone.und.0.safe_value'),
                        'telephone2' => $this->keyData($user, 'field_telephone2.und.0.safe_value'),
                        'fax' => $this->keyData($user, 'field_fax.und.0.safe_value'),
                        'position' => $this->keyData($user, 'field_fonction_du_demandeur.und.0.safe_value'),
                        'organism' => $this->keyData($user, 'field_organisme.und.0.safe_value'),
                        'year_of_alternance' => $this->keyData($user, 'field_annee_internat.und.0.safe_value'),
                        'current_speciality' => $this->keyData($user, 'field_statut_actuel.und.0.tid'),
                        'comment' => $this->keyData($user, 'field_commentaire_user.und.0.safe_value'),
                        'create_at' => date('Y-m-d H:i', $user['created']),
                        'user_comment' => $this->keyData($user, 'field_commentaires_remplacant.und.0.safe_value'),
                        'cv' => $this->keyData($user, 'field_cv.und.0.filename'),
                        // 'diplom' => ,
                        // 'licence' => ,
                    ];

                    $this->connection->insert('user', $userData);

                    // La relation clinic est traite independament
                    // $this->storeClinic($user);

                    $this->storeRoles($user);
                    $this->storeSubSpecialities($user);
                    $this->storeRegions($user);

                    array_push($validUser, $user['uid']);
                } else {
                    array_push($invalidUser, $user['uid']);
                }
            }

            $this->connection->commit();

            // Logging

            echo sprintf('Utilisateur du %s au %s', $premierUser, $dernierUser). PHP_EOL;
            echo sprintf('Utilisateur valide: %s (%d)', implode(', ', $validUser), count($validUser)). PHP_EOL;
            echo sprintf('Utilisateur invalide: %s (%d)', implode(', ', $invalidUser), count($invalidUser)). PHP_EOL;

        } catch (\Exception $e) {
            $this->connection->rollBack();

            echo 'Utilisateur qui a echoue: ' . $dernierUser . PHP_EOL;

            throw $e;
        }
    }

    private function isUserValid(array $user): bool
    {
        return !empty($user['mail']);
    }

    private function storeAddress(array $user): ?int
    {
        // on utilise user id pour l'id de l'adresse puisque drupal n'a pas d'id adresse
        $address = $this->keyData($user, 'field_adresse.und.0');

        if (empty($address)) {
            return null;
        }

        $addressData = [
            'id' => $user['uid'],
            'country' => $address['country'],
            'locality' => $address['locality'],
            'postal_code' => $address['postal_code'],
            'thoroughfare' => $address['thoroughfare'],
            'premise' => $address['premise'],
        ];

        $this->connection->insert('user_address', $addressData);

        return intval($user['uid']);
    }

    private function storeEstablishment(array $user) : ?int
    {
        // on utilise user id pour l'id de l'etablissement puisque drupal n'a pas d'id etablissement
        $nom = $this->keyData($user, 'field_nom_etablissement.und.0.safe_value');
        $nombreLits = $this->keyData($user, 'field_nombre_de_lits.und.0.safe_value');
        $siteInternet = $this->keyData($user, 'field_site_internet.und.0.safe_value');
        $nombreConsultations = $this->keyData($user, 'field_nombre_de_consultations.und.0.value');
        $consultationPar = $this->keyData($user, 'field_par.und.0.value');
        $nomChefService = $this->keyData($user, 'field_nom_du_chef_de_service.und.0.safe_value');

        // le nom est requis
        if (empty($nom) && empty($nombreLits) && empty($siteInternet) && empty($nombreConsultations) && empty($consultationPar) && empty($nomChefService)) {
            return null;
        }

        $establishmentData = [
            'id' => $user['uid'],
            'name' => $nom,
            'beds_count' => $nombreLits,
            'site_web' => $siteInternet,
            'consultation_count' => $nombreConsultations,
            'per' => $consultationPar,
            // 'service_name' => $establishment['premise'],
            'chief_service_name' => $nomChefService,
        ];

        $this->connection->insert('user_establishment', $establishmentData);

        return intval($user['uid']);
    }

    private function storeSpeciality(array $user) : ?int
    {
        $specialityId = $this->keyData($user, 'field_specialite.und.0.tid');
        
        return $specialityId === null ? null : intval($specialityId);
    }

    private function storeSubscription(array $user) : ?int
    {
        // on utilise user id pour l'id de l'abonnement puisque drupal n'a pas d'id abonnement
        $valableJusquAu = $this->keyData($user, 'field_valable_jusqu_au.und.0.value');
        $statut = $this->keyData($user, 'field_abonnement_actif.und.0.value');
        $emailFin = $this->keyData($user, 'field_email_fin_abonnement.und.0.value');
        $nombreInstallationRestante = $this->keyData($user, 'field_demande_installation.und.0.value');

        // le nom est requis
        if (empty($valableJusquAu) && empty($statut) && empty($emailFin) && empty($nombreInstallationRestante)) {
            return null;
        }

        $statut = $statut === null ? false : intval($statut) === 1; // 1 = active, 0 = inactive
        $emailFin = $emailFin === null ? false : intval($emailFin) === 1; // 1 = oui, 0 = non

        $subscriptionData = [
            'id' => $user['uid'],
            'end_at' => $valableJusquAu,
            'status' => $statut ? 0 : 1,
            'end_notification' => $emailFin ? 0 : 1,
            'installation_count' => $nombreInstallationRestante,
        ];

        $this->connection->insert('user_subscription', $subscriptionData);

        return intval($user['uid']);
    }

    private function storeRoles(array $user)
    {
        $roles = $user['roles'];

        foreach($roles as $roleId => $role) {
            $this->connection->insert('user_user_role', [
                'user_id' => $user['uid'],
                'user_role_id' => $roleId,
            ]);
        }
    }

    private function storeSubSpecialities(array $user)
    {
        $specialities = $this->keyData($user, 'field_sous_specialite.und');
        
        if (!empty($specialities)) {
            $specialitiesId = array_map(function ($row) { return +$row['tid']; }, $specialities);

            // remove duplicate
            $specialitiesId = array_unique($specialitiesId, SORT_NUMERIC);

            sort($specialitiesId);

            foreach($specialitiesId as $speciality) {
                $this->connection->insert('user_speciality', [
                    'user_id' => $user['uid'],
                    'speciality_id' => $speciality,
                ]);
            }
        }
    }

    private function storeRegions(array $user)
    {
        $regions = $this->keyData($user, 'field_mobilite.und');

        if (!empty($regions)) {
            $regionsId = array_map(function ($row) { return +$row['tid']; }, $regions);

            // remove duplicate
            $regionsId = array_unique($regionsId, SORT_NUMERIC);

            sort($regionsId);

            foreach($regionsId as $region) {
                $this->connection->insert('user_region', [
                    'user_id' => $user['uid'],
                    'region_id' => $region,
                ]);
            }
        }
    }
}