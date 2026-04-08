<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

// Create multiple DAG items at once
if (isset($_POST['create_multiple'])) {
    $customer_id = $_POST['customer_id'];
    $dag_received_date = $_POST['dag_received_date'];
    $vehicle_number = $_POST['vehicle_number'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $items = json_decode($_POST['items'], true);

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'No items to save.']);
        exit();
    }

    $success = true;
    $count = 0;
    
    // Get the next sequential DAG number once for the entire batch
    $DAG_CUSTOMER_HELPER = new DagCustomer(NULL);
    $next_num = $DAG_CUSTOMER_HELPER->getNextId();
    $batch_dag_number = 'DAG-' . str_pad($next_num, 5, "0", STR_PAD_LEFT);

    foreach ($items as $item) {
        $DAG_CUSTOMER = new DagCustomer(NULL);
        $DAG_CUSTOMER->customer_id = $customer_id;
        $DAG_CUSTOMER->my_number = $item['my_number'];
        $DAG_CUSTOMER->size = $item['size'];
        $DAG_CUSTOMER->brand = $item['brand'];
        $DAG_CUSTOMER->serial_no = $item['serial_no'];
        $DAG_CUSTOMER->dag_received_date = $dag_received_date;
        $DAG_CUSTOMER->vehicle_number = $vehicle_number;
        $DAG_CUSTOMER->remark = $remark;

        $result = $DAG_CUSTOMER->create($batch_dag_number);
        if ($result) {
            $count++;
        } else {
            $success = false;
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => $count . ' DAG item(s) saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Some items failed to save. ' . $count . ' of ' . count($items) . ' saved.']);
    }
    exit();
}

if (isset($_POST['create'])) {

    $DAG_CUSTOMER = new DagCustomer(NULL);
    $DAG_CUSTOMER->customer_id = $_POST['customer_id'];
    $DAG_CUSTOMER->my_number = $_POST['my_number'];
    $DAG_CUSTOMER->size = $_POST['size'];
    $DAG_CUSTOMER->brand = $_POST['brand'];
    $DAG_CUSTOMER->serial_no = $_POST['serial_no'];
    $DAG_CUSTOMER->dag_received_date = $_POST['dag_received_date'];
    $DAG_CUSTOMER->vehicle_number = $_POST['vehicle_number'];
    $DAG_CUSTOMER->remark = $_POST['remark'];

    $result = $DAG_CUSTOMER->create();

    if ($result) {
        $result = ["status" => 'success'];
        echo json_encode($result);
        exit();
    } else {
        $result = ["status" => 'error'];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST['update'])) {

    $DAG_CUSTOMER = new DagCustomer($_POST['id']);
    $DAG_CUSTOMER->customer_id = $_POST['customer_id'];
    $DAG_CUSTOMER->my_number = $_POST['my_number'];
    $DAG_CUSTOMER->size = $_POST['size'];
    $DAG_CUSTOMER->brand = $_POST['brand'];
    $DAG_CUSTOMER->serial_no = $_POST['serial_no'];
    $DAG_CUSTOMER->dag_received_date = $_POST['dag_received_date'];
    $DAG_CUSTOMER->vehicle_number = $_POST['vehicle_number'];
    $DAG_CUSTOMER->remark = $_POST['remark'];

    $result = $DAG_CUSTOMER->update();

    if ($result) {
        $result = ["status" => 'success'];
        echo json_encode($result);
        exit();
    } else {
        $result = ["status" => 'error'];
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST['delete'])) {
    $DAG_CUSTOMER = new DagCustomer(NULL);
    if (!empty($_POST['dag_number'])) {
        $result = $DAG_CUSTOMER->deleteByDagNumber($_POST['dag_number']);
    } elseif (isset($_POST['id'])) {
        $DAG_CUSTOMER->__construct($_POST['id']);
        $result = $DAG_CUSTOMER->delete();
    } else {
        $result = false;
    }

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

if (isset($_POST['get_next_id'])) {
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $next_id = $DAG_CUSTOMER->getNextId();
    echo json_encode(['status' => 'success', 'next_id' => 'DAG-' . str_pad($next_id, 5, "0", STR_PAD_LEFT)]);
    exit();
}

if (isset($_POST['get_dag_items'])) {
    $dag_number = $_POST['dag_number'];
    $DAG_CUSTOMER = new DagCustomer(NULL);
    $items = $DAG_CUSTOMER->getItemsByDagNumber($dag_number);
    echo json_encode(['status' => 'success', 'data' => $items]);
    exit();
}

