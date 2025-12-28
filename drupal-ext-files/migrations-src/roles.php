<?php
$query = db_select('role', 'r');
$query->fields('r', array('rid', 'name'));
$query->orderBy('r.rid', 'ASC');

$result = $query->execute()->fetchAll();

drupal_json_output($result);