<?php
$query = db_select('node', 'n');
$query->condition('n.type', 't_moignages', '=');
$query->fields('n', array('nid'));
$result = $query->execute();

$json = [];
foreach ($result as $record) {
    $json[$record->nid] = node_load($record->nid);
}

drupal_json_output($json);