<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ------- Load DAG-level data (parent rows) -------
    if ($action === 'load_dags' || (isset($_POST['filter']) && $_POST['filter'] == 'true')) {
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        $dag_no = isset($_POST['dag_no']) ? trim($_POST['dag_no']) : '';
        $my_number = isset($_POST['my_number']) ? trim($_POST['my_number']) : '';
        $belt_id = isset($_POST['belt_id']) ? trim($_POST['belt_id']) : '';
        $size_id = isset($_POST['size_id']) ? trim($_POST['size_id']) : '';

        $DAG = new Dag();
        $dags = $DAG->getFilteredDags($status, $dag_no, $my_number, $belt_id, $size_id);

        echo json_encode([
            'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            'recordsTotal' => count($dags),
            'recordsFiltered' => count($dags),
            'data' => $dags,
        ]);
        exit;
    }

    // ------- Load DAG items for a specific DAG (child rows) -------
    if ($action === 'get_dag_items') {
        $dag_id = isset($_POST['dag_id']) ? intval($_POST['dag_id']) : 0;

        if ($dag_id > 0) {
            $DAG_ITEM = new DagItem();
            $items = $DAG_ITEM->getByValuesDagId($dag_id);

            echo json_encode([
                'status' => 'success',
                'data' => $items,
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid DAG ID',
            ]);
        }
        exit;
    }

    // ------- Legacy filter action (backward compat) -------
    if ($action === 'filter') {
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
}

echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request',
]);
exit;

?>