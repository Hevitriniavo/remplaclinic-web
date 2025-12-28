<?php
// q=users&uid[0]=67218&uid[1]=67480&uid[2]=67457&uid[3]=42400
$query = db_select('users', 'u');

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$count = isset($_GET['count']) ? true : false;

if (!empty($_GET['uid'])) {
    $query->condition('u.uid', $_GET['uid'], is_array($_GET['uid']) ? 'IN' : '=');
}

if (!empty($_GET['gt_uid'])) {
    $query->condition('u.uid', $_GET['gt_uid'], '>');
}

// fields
if ($count) {
    $result = $query->countQuery()->execute()->fetchField();
    
    drupal_json_output(['total_count' => $result]);
} else {
    $query->fields('u', array('uid'));

    $query->range($offset, $limit);
    
    $result = $query->execute();
    
    $json = [];
    foreach ($result as $record) {
        $json[$record->uid] = user_load($record->uid);
    }
    
    drupal_json_output($json);
}
