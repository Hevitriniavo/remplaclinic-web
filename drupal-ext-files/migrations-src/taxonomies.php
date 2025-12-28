<?php
// table taxonomy_term_data, taxonomy_term_hierarchy, taxonomy_vocabulary, taxonomy_index

// Get all terms from the given vocabulary (e.g., 'tags')
// Retrieve the vocabulary by its machine name given in query parameter.

$vocabulary = null;
$terms = [];

if (isset($_GET['vocabulary'])) {
    $vocabulary = taxonomy_vocabulary_machine_name_load($_GET['vocabulary']);
    
    $terms = taxonomy_get_tree($vocabulary->vid);
}

drupal_json_output($terms);