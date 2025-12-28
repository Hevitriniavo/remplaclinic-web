<?php
$query = db_select('field_data_field_clinique', 'fdfc');
$query->fields('fdfc', array('entity_id', 'field_clinique_uid'));

$result = $query->execute()->fetchAll();

drupal_json_output($result);