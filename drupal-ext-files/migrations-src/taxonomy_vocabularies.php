<?php
// table taxonomy_vocabularies
$vocabularies = taxonomy_get_vocabularies();

drupal_json_output($vocabularies);