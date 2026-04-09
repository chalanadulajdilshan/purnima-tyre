<?php

include '../../class/include.php';
header('Content-Type: application/json; charset=UTF8');

// ==========================================
// GET NEXT INVOICE NUMBER
// ==========================================
if (isset($_POST['get_next_id'])) {
    $INVOICE = new DagInvoice(NULL);
    $next_id = $INVOICE->getNextId();
    $invoice_number = 'DINV-' . str_pad($next_id, 5, "0", STR_PAD_LEFT);
    echo json_encode(['status' => 'success', 'next_id' => $invoice_number]);
    exit();
}

// ==========================================
// SEARCH CUSTOMERS (only those with un-invoiced DAGs)
// ==========================================
if (isset($_POST['search_customer'])) {
    $keyword = $_POST['keyword'] ?? '';
    $db = Database::getInstance();
    $keyword = $db->escapeString($keyword);

    $query = "SELECT DISTINCT c.id, c.code, c.name, c.name_2
              FROM `customer_master` c 
              INNER JOIN `dag_customers` dc ON c.id = dc.customer_id
              WHERE (c.name LIKE '%$keyword%' 
                 OR c.code LIKE '%$keyword%'
                 OR c.name_2 LIKE '%$keyword%')
                AND dc.is_invoiced = 0
                AND dc.is_cancelled = 0
              ORDER BY c.name ASC 
              LIMIT 20";

    $result = $db->readQuery($query);
    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['full_name'] = trim($row['name'] . ' ' . ($row['name_2'] ?? ''));
        $customers[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $customers]);
    exit();
}

// ==========================================
// SEARCH DAG ITEMS (un-invoiced, for selection)
// ==========================================
if (isset($_POST['search_dag_items'])) {
    $keyword = $_POST['keyword'] ?? '';
    $db = Database::getInstance();
    $keyword = $db->escapeString($keyword);

    $query = "SELECT dc.id, dc.dag_number, dc.my_number, dc.size, dc.brand, dc.serial_no,
                     dc.dag_received_date, dc.vehicle_number, dc.customer_id, dc.cost,
                     c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                     cm.name as company_name
              FROM `dag_customers` dc
              LEFT JOIN `customer_master` c ON dc.customer_id = c.id
              INNER JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
              INNER JOIN `dag_company_assignments` dca ON dcai.assignment_id = dca.id
              INNER JOIN `company_master` cm ON dca.company_id = cm.id
              WHERE dc.is_invoiced = 0
                AND dc.is_cancelled = 0
                AND dcai.id = (SELECT MAX(id) FROM dag_company_assignment_items WHERE dag_id = dc.id)
                AND dcai.company_status != 'Processing'
                AND (dc.my_number LIKE '%$keyword%' 
                     OR dc.serial_no LIKE '%$keyword%' 
                     OR dc.dag_number LIKE '%$keyword%'
                     OR c.name LIKE '%$keyword%')
              ORDER BY dc.id DESC LIMIT 30";

    $result = $db->readQuery($query);
    $dags = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['customer_full_name'] = trim($row['customer_name'] . ' ' . ($row['customer_name_2'] ?? ''));
        $dags[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $dags]);
    exit();
}

// ==========================================
// SAVE NEW INVOICE
// ==========================================
if (isset($_POST['create'])) {
    $customer_id = $_POST['customer_id'] ?? null;
    $payment_mode = $_POST['payment_mode'] ?? 'cash';
    $items = $_POST['items'] ?? [];

    if (is_string($items)) {
        $items = json_decode($items, true);
    }

    if (empty($customer_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Please select a customer.']);
        exit();
    }

    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'No items to save.']);
        exit();
    }

    // Calculate totals
    $sub_total = 0;
    $discount_total = 0;
    $grand_total = 0;
    foreach ($items as $item) {
        $price = floatval($item['price'] ?? 0);
        $discountPct = floatval($item['discount'] ?? 0);
        $discountAmt = $price * $discountPct / 100;
        $total = floatval($item['total'] ?? 0);
        $sub_total += $price;
        $discount_total += $discountAmt;
        $grand_total += $total;
    }

    $INVOICE = new DagInvoice(NULL);
    $INVOICE->customer_id = $customer_id;
    $INVOICE->payment_mode = $payment_mode;
    $INVOICE->invoice_date = date('Y-m-d');
    $INVOICE->sub_total = $sub_total;
    $INVOICE->discount_total = $discount_total;
    $INVOICE->grand_total = $grand_total;

    $createdInvoice = $INVOICE->create();

    if ($createdInvoice) {
        $addSuccess = $createdInvoice->addItems($items);
        if ($addSuccess) {
            echo json_encode(['status' => 'success', 'invoice_id' => $createdInvoice->id, 'invoice_number' => $createdInvoice->invoice_number]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add some items.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create invoice.']);
    }
    exit();
}

// ==========================================
// UPDATE INVOICE
// ==========================================
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? null;
    $customer_id = $_POST['customer_id'] ?? null;
    $payment_mode = $_POST['payment_mode'] ?? 'cash';
    $items = $_POST['items'] ?? [];

    if (is_string($items)) {
        $items = json_decode($items, true);
    }

    if (empty($id) || empty($customer_id) || empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing data.']);
        exit();
    }

    // Calculate totals
    $sub_total = 0;
    $discount_total = 0;
    $grand_total = 0;
    foreach ($items as $item) {
        $price = floatval($item['price'] ?? 0);
        $discountPct = floatval($item['discount'] ?? 0);
        $discountAmt = $price * $discountPct / 100;
        $total = floatval($item['total'] ?? 0);
        $sub_total += $price;
        $discount_total += $discountAmt;
        $grand_total += $total;
    }

    $INVOICE = new DagInvoice($id);
    $INVOICE->customer_id = $customer_id;
    $INVOICE->payment_mode = $payment_mode;
    $INVOICE->sub_total = $sub_total;
    $INVOICE->discount_total = $discount_total;
    $INVOICE->grand_total = $grand_total;

    $updatedInvoice = $INVOICE->update();

    if ($updatedInvoice) {
        $updatedInvoice->deleteItems();

        $addSuccess = $updatedInvoice->addItems($items);
        if ($addSuccess) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update some items.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update invoice.']);
    }
    exit();
}

// ==========================================
// DELETE INVOICE
// ==========================================
if (isset($_POST['delete'])) {
    $id = (int) $_POST['id'];
    $INVOICE = new DagInvoice($id);

    if ($INVOICE->id) {
        if ($INVOICE->delete()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete invoice.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
    }
    exit();
}

// ==========================================
// CANCEL INVOICE
// ==========================================
if (isset($_POST['cancel_invoice'])) {
    $id = (int) $_POST['id'];
    $INVOICE = new DagInvoice($id);

    if ($INVOICE->id) {
        if ($INVOICE->cancel()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to cancel invoice.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
    }
    exit();
}

// ==========================================
// SEARCH INVOICES (for the search modal)
// ==========================================
if (isset($_POST['search_invoice'])) {
    $keyword = $_POST['keyword'] ?? '';
    $INVOICE = new DagInvoice(NULL);
    $invoices = $INVOICE->searchInvoices($keyword);
    echo json_encode(['status' => 'success', 'data' => $invoices]);
    exit();
}

// ==========================================
// GET INVOICE ITEMS (for loading an existing invoice)
// ==========================================
if (isset($_POST['get_invoice_items'])) {
    $invoice_id = (int) $_POST['invoice_id'];
    $INVOICE = new DagInvoice($invoice_id);
    $items = $INVOICE->getItems();
    echo json_encode(['status' => 'success', 'data' => $items, 'invoice' => [
        'id' => $INVOICE->id,
        'invoice_number' => $INVOICE->invoice_number,
        'customer_id' => $INVOICE->customer_id,
        'payment_mode' => $INVOICE->payment_mode,
        'invoice_date' => $INVOICE->invoice_date,
        'is_cancelled' => $INVOICE->is_cancelled
    ]]);
    exit();
}
