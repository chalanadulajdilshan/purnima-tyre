<?php
class DagInvoice
{
    public $id;
    public $invoice_number;
    public $customer_id;
    public $payment_mode;
    public $invoice_date;
    public $sub_total;
    public $discount_total;
    public $grand_total;
    public $is_cancelled;
    public $created_at;
    public $updated_at;

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `dag_invoices` WHERE `id` = " . (int) $id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->invoice_number = $result['invoice_number'];
                $this->customer_id = $result['customer_id'];
                $this->payment_mode = $result['payment_mode'];
                $this->invoice_date = $result['invoice_date'];
                $this->sub_total = $result['sub_total'];
                $this->discount_total = $result['discount_total'];
                $this->grand_total = $result['grand_total'];
                $this->is_cancelled = $result['is_cancelled'];
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
        }
    }

    public function create()
    {
        $query = "INSERT INTO `dag_invoices` (`customer_id`, `payment_mode`, `invoice_date`, `sub_total`, `discount_total`, `grand_total`) "
            . "VALUES ('" . $this->customer_id . "', '" . $this->payment_mode . "', '" . $this->invoice_date . "', '"
            . ($this->sub_total ?? 0) . "', '" . ($this->discount_total ?? 0) . "', '" . ($this->grand_total ?? 0) . "')";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $last_id = mysqli_insert_id($db->DB_CON);
            $invoice_number = 'DINV-' . str_pad($last_id, 5, "0", STR_PAD_LEFT);
            $update_query = "UPDATE `dag_invoices` SET `invoice_number` = '" . $invoice_number . "' WHERE `id` = " . $last_id;
            $db->readQuery($update_query);

            $this->__construct($last_id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function update()
    {
        $query = "UPDATE `dag_invoices` SET "
            . "`customer_id` = '" . $this->customer_id . "', "
            . "`payment_mode` = '" . $this->payment_mode . "', "
            . "`invoice_date` = '" . $this->invoice_date . "', "
            . "`sub_total` = '" . ($this->sub_total ?? 0) . "', "
            . "`discount_total` = '" . ($this->discount_total ?? 0) . "', "
            . "`grand_total` = '" . ($this->grand_total ?? 0) . "' "
            . "WHERE `id` = '" . $this->id . "'";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $this->__construct($this->id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function delete()
    {
        $this->deleteItems();
        $query = "DELETE FROM `dag_invoices` WHERE id = '" . $this->id . "'";
        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    public function cancel()
    {
        $query = "UPDATE `dag_invoices` SET `is_cancelled` = 1 WHERE `id` = '" . $this->id . "'";
        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            // Also mark the dag_customers items as not invoiced and not cancelled
            $items = $this->getItems();
            foreach ($items as $item) {
                // Return items to 'Available' state
                $dq = "UPDATE `dag_customers` SET `is_invoiced` = 0, `is_cancelled` = 0 WHERE `id` = " . (int) $item['dag_id'];
                $db->readQuery($dq);
            }
            $this->__construct($this->id);
            return $this;
        }
        return FALSE;
    }

    public function getNextId()
    {
        $db = Database::getInstance();
        $query = "SELECT invoice_number FROM `dag_invoices` ORDER BY id DESC LIMIT 1";
        $result = mysqli_fetch_assoc($db->readQuery($query));
        $next_num = 1;
        if ($result && $result['invoice_number']) {
            $next_num = (int) str_replace('DINV-', '', $result['invoice_number']) + 1;
        }
        return $next_num;
    }

    // Item Management
    public function addItems($items)
    {
        $db = Database::getInstance();
        $success = true;

        foreach ($items as $item) {
            $dag_id = (int) $item['dag_id'];
            $cost = floatval($item['cost'] ?? 0);
            $price = floatval($item['price'] ?? 0);
            $discount = floatval($item['discount'] ?? 0);
            $total = floatval($item['total'] ?? 0);
            $issued_date = !empty($item['issued_date']) ? "'" . $item['issued_date'] . "'" : "NULL";

            $query = "INSERT INTO `dag_invoice_items` (`invoice_id`, `dag_id`, `cost`, `price`, `discount`, `total`, `issued_date`) "
                . "VALUES ('" . $this->id . "', '" . $dag_id . "', '" . $cost . "', '" . $price . "', '" . $discount . "', '" . $total . "', " . $issued_date . ")";

            $result = $db->readQuery($query);
            if (!$result) {
                $success = false;
            }

            // Also mark dag_customers as invoiced
            $update_dag = "UPDATE `dag_customers` SET `is_invoiced` = 1, `is_cancelled` = 0, "
                . "`cost` = '" . $cost . "', `price` = '" . $price . "', `discount` = '" . $discount . "', "
                . "`total` = '" . $total . "', `issued_date` = " . $issued_date . " "
                . "WHERE `id` = " . $dag_id;
            $db->readQuery($update_dag);
        }
        return $success;
    }

    public function getItems()
    {
        $query = "SELECT dii.*, dc.dag_number, dc.my_number, dc.size, dc.brand, dc.serial_no, dc.dag_received_date,
                         dc.vehicle_number, dc.customer_id,
                         c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         (SELECT cm.name FROM dag_company_assignment_items dcai
                          JOIN dag_company_assignments dca ON dcai.assignment_id = dca.id
                          JOIN company_master cm ON dca.company_id = cm.id
                          WHERE dcai.dag_id = dii.dag_id
                          ORDER BY dcai.id DESC LIMIT 1) as company_name
                  FROM `dag_invoice_items` dii
                  LEFT JOIN `dag_customers` dc ON dii.dag_id = dc.id
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id
                  WHERE dii.`invoice_id` = " . (int) $this->id;

        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $array_res[] = $row;
        }

        return $array_res;
    }

    public function deleteItems()
    {
        $db = Database::getInstance();
        // First, un-mark dag_customers as invoiced
        $items = $this->getItems();
        foreach ($items as $item) {
            $dq = "UPDATE `dag_customers` SET `is_invoiced` = 0, `cost` = 0, `price` = 0, `discount` = 0, `total` = 0, `issued_date` = NULL WHERE `id` = " . (int) $item['dag_id'];
            $db->readQuery($dq);
        }
        $query = "DELETE FROM `dag_invoice_items` WHERE `invoice_id` = " . (int) $this->id;
        return $db->readQuery($query);
    }

    // Search invoices
    public function searchInvoices($keyword = '')
    {
        $db = Database::getInstance();
        $keyword = $db->escapeString($keyword);

        $query = "SELECT di.*, c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         (SELECT COUNT(*) FROM dag_invoice_items WHERE invoice_id = di.id) as item_count
                  FROM `dag_invoices` di
                  LEFT JOIN `customer_master` c ON di.customer_id = c.id
                  WHERE (di.invoice_number LIKE '%$keyword%'
                         OR c.name LIKE '%$keyword%'
                         OR c.code LIKE '%$keyword%')
                  ORDER BY di.id DESC LIMIT 20";

        $result = $db->readQuery($query);
        $invoices = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $row['customer_full_name'] = trim($row['customer_name'] . ' ' . ($row['customer_name_2'] ?? ''));
            $invoices[] = $row;
        }

        return $invoices;
    }
}
?>
