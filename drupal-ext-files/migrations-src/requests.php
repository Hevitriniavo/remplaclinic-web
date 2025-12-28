<?php

$allowedTypes = [
    'demande_de_remplacement',
    'demande_d_installation',
];

// get all nodes of type 'demande_de_remplacement' in pagination.
// the limit and offset can be passed as query parameters.
// And node type can be changed using 'type' query parameter.
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$count = isset($_GET['count']) ? true : false;
$type = isset($_GET['type']) ? $_GET['type'] : null;

// build the query
$query = db_select('node', 'n');

// filterby type
if (is_null($type)) {
    $query->condition('n.type', $allowedTypes, 'IN');
} else if (!in_array($type, $allowedTypes)) {
    // si le type n'est pas parmis les types autorises alors on retourne rien
    $query->condition('n.nid', 0, '<');
} else {
    $query->condition('n.type', $type, '=');
}

// filter by nid
if (!empty($_GET['nid'])) {
    $query->condition('n.nid', $_GET['nid'], is_array($_GET['nid']) ? 'IN' : '=');
}

if (!empty($_GET['gt_nid'])) {
    $query->condition('n.nid', $_GET['gt_nid'], '>');
}

// fields
if ($count) {
    $result = $query->countQuery()->execute()->fetchField();
    
    drupal_json_output(['total_count' => $result]);
} else {
    // select only the nid field
    $query->fields('n', array('nid', 'type'));

    // apply pagination
    $query->range($offset, $limit);

    $result = $query->execute();

    $json = [];
    foreach ($result as $record) {
        $json[$record->nid] = node_load($record->nid);

        // Do this step independantly because it consume more memory

        // // candidature
        // $queryCandidature = db_select($record->type === 'demande_de_remplacement' ? 'candidature_remplacement' : 'candidature_installation', 'c');
        // // seulement les utilisateurs existnt
        // $queryCandidature->join('users', 'u', 'u.uid = c.uid');
        // $queryCandidature->condition('c.uid', '0', '<>');

        // // seulement ce qui correspond a la demande
        // $queryCandidature->condition('c.nid', $record->nid, '=');


        // $queryCandidature->fields('c', array('id', 'nid', 'uid', 'statut', 'envoi_mail'));

        // $json[$record->nid]->candidatures = $queryCandidature->execute()->fetchAll();
    }

    drupal_json_output($json);
}