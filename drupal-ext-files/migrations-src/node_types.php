<?php
// table node_type
$types = node_type_get_types();

drupal_json_output($types);