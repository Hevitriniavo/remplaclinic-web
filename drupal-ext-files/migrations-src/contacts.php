<?php
// q=users&uid[0]=67218&uid[1]=67480&uid[2]=67457&uid[3]=42400
$query = db_select('webform_submissions', 's');
$query->join('node', 'n', 'n.nid = s.nid');

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$count = isset($_GET['count']) ? true : false;

// sid
if (!empty($_GET['sid'])) {
    $query->condition('s.sid', $_GET['sid'], is_array($_GET['sid']) ? 'IN' : '=');
}

if (!empty($_GET['gt_sid'])) {
    $query->condition('s.sid', $_GET['gt_sid'], '>');
}

// nid
if (!empty($_GET['nid'])) {
    $query->condition('s.nid', $_GET['nid'], is_array($_GET['nid']) ? 'IN' : '=');
}

if (!empty($_GET['gt_nid'])) {
    $query->condition('s.nid', $_GET['gt_nid'], '>');
}

// submitted date
if (!empty($_GET['gt_submitted'])) {
    $query->condition('s.submitted', $_GET['gt_submitted'], '>');
}

// draft
if (isset($_GET['draft'])) {
    $query->condition('s.is_draft', $_GET['draft'], '=');
} else {
    $query->condition('s.is_draft', 1, '<>');
}

// fields
if ($count) {
    $result = $query->countQuery()->execute()->fetchField();
    
    drupal_json_output(['total_count' => $result]);
} else {
    $query->fields('s', array('sid', 'nid', 'uid', 'submitted', 'remote_addr'));

    $query->range($offset, $limit);
    
    $result = $query->execute();
    
    $json = [];
    foreach ($result as $record) {
        $row = [
            'submission' => $record,
        ];

        $submittedDataQuery = db_select('webform_submitted_data', 'sd');
        $submittedDataQuery->join('webform_component', 'wc', 'wc.cid = sd.cid AND wc.nid = sd.nid');
        $submittedDataQuery->condition('sd.sid', $record->sid, '=');
        $submittedDataQuery->fields('sd', array('nid', 'sid', 'cid', 'no', 'data'));
        $submittedDataQuery->fields('wc', array('form_key', 'name', 'type', 'value', 'mandatory'));

        $records = $submittedDataQuery->execute();

        $rowData = [];
        foreach($records as $recordData) {
            $rowData[$recordData->form_key] = $recordData->data;
        }

        $row['data'] = $rowData;

        $json[] = $row;
    }
    
    drupal_json_output($json);
}
