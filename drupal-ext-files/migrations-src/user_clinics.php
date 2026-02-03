<?php
$query = db_select('field_data_field_clinique', 'fdfc');

$query->join('users', 's', 's.uid = fdfc.entity_id');
$query->join('users', 't', 't.uid = fdfc.field_clinique_uid');

$query->fields('fdfc', array('entity_id', 'field_clinique_uid'));

$result = $query->execute()->fetchAll();

drupal_json_output($result);