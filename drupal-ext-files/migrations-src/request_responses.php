<?php

$allowedTypes = [
    'candidature_remplacement',
    'candidature_installation',
];

// get all nodes of type 'demande_de_remplacement' in pagination.
// the limit and offset can be passed as query parameters.
// And node type can be changed using 'type' query parameter.
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 25;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$count = isset($_GET['count']) ? true : false;
$type = isset($_GET['type']) ? $_GET['type'] : null;

// filter by type
if (is_null($type) || !in_array($type, $allowedTypes)) {
    throw new Exception('Tye must be in: '. implode(',', $allowedTypes));
}

// build the query
$query = db_select($type, 'c');
// seulement les utilisateurs existants
$query->join('users', 'u', 'u.uid = c.uid');
$query->condition('c.uid', '0', '<>');

// seulement les demandes existantes
$query->join('node', 'n', 'n.nid = c.nid');
$query->condition('n.uid', '0', '<>');

// filter by id
if (!empty($_GET['id'])) {
    $query->condition('c.id', $_GET['id'], is_array($_GET['id']) ? 'IN' : '=');
}

if (!empty($_GET['gt_id'])) {
    $query->condition('c.id', $_GET['gt_id'], '>');
}

// filter by nid
if (!empty($_GET['nid'])) {
    $query->condition('c.nid', $_GET['nid'], is_array($_GET['nid']) ? 'IN' : '=');
}

if (!empty($_GET['gt_nid'])) {
    $query->condition('c.nid', $_GET['gt_nid'], '>');
}

// filter by uid
if (!empty($_GET['uid'])) {
    $query->condition('c.uid', $_GET['uid'], is_array($_GET['uid']) ? 'IN' : '=');
}

if (!empty($_GET['gt_uid'])) {
    $query->condition('c.uid', $_GET['gt_uid'], '>');
}

// fields
if ($count) {
    $result = $query->countQuery()->execute()->fetchField();
    
    drupal_json_output(['total_count' => $result]);
} else {
    // select only the nid field
    $query->fields('c', array('id', 'nid', 'uid', 'statut', 'envoi_mail'));;

    // apply pagination
    $query->orderBy('c.id', 'ASC');
    $query->range($offset, $limit);

    $result = $query->execute()->fetchAll();

    drupal_json_output(['candidatures' => $result]);
}