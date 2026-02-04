<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'filter') {
    $from_date = isset($_POST['from_date']) ? trim($_POST['from_date']) : '';
    $to_date = isset($_POST['to_date']) ? trim($_POST['to_date']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $dag_no = isset($_POST['dag_no']) ? trim($_POST['dag_no']) : '';
    $my_number = isset($_POST['my_number']) ? trim($_POST['my_number']) : '';
    $belt_id = isset($_POST['belt_id']) ? trim($_POST['belt_id']) : '';
    $size_id = isset($_POST['size_id']) ? trim($_POST['size_id']) : '';

    $DAG_REPORT = new Dag();
    $reports = $DAG_REPORT->getFilteredReports($from_date, $to_date, $status, $dag_no, $my_number, $belt_id, $size_id);

    echo json_encode([
        'status' => 'success',
        'from_date' => $from_date,
        'to_date' => $to_date,
        'reports' => $reports,
    ]);
    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request',
]);
exit;

?>