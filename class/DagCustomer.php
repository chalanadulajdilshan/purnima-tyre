<?php
/**
 * Description of DagCustomer
 *
 * @author Wimali
 */
class DagCustomer
{

    public $id;
    public $customer_id;
    public $dag_number;
    public $my_number;
    public $size;
    public $brand;
    public $serial_no;
    public $dag_received_date;
    public $vehicle_number;
    public $remark;
    public $cost;
    public $price;
    public $discount;
    public $total;
    public $is_invoiced;
    public $is_cancelled;
    public $issued_date;
    public $created_at;
    public $updated_at;

    public function getDagNumber()
    {
        return $this->dag_number;
    }

    public function __construct($id = null)
    {
        if ($id) {
            $query = "SELECT * FROM `dag_customers` WHERE `id`=" . (int) $id;
            $db = Database::getInstance();
            $result = mysqli_fetch_array($db->readQuery($query));

            if ($result) {
                $this->id = $result['id'];
                $this->customer_id = $result['customer_id'];
                $this->dag_number = $result['dag_number'];
                $this->my_number = $result['my_number'];
                $this->size = $result['size'];
                $this->brand = $result['brand'];
                $this->serial_no = $result['serial_no'];
                $this->dag_received_date = $result['dag_received_date'];
                $this->vehicle_number = $result['vehicle_number'];
                $this->remark = $result['remark'];
                $this->cost = $result['cost'] ?? 0;
                $this->price = $result['price'] ?? 0;
                $this->discount = $result['discount'] ?? 0;
                $this->total = $result['total'] ?? 0;
                $this->is_invoiced = $result['is_invoiced'] ?? 0;
                $this->is_cancelled = $result['is_cancelled'] ?? 0;
                $this->issued_date = $result['issued_date'] ?? null;
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
        }
    }

    public function create($dag_number = null)
    {
        $query = "INSERT INTO `dag_customers` (`customer_id`, `my_number`, `size`, `brand`, `serial_no`, `dag_received_date`, `vehicle_number`, `remark`, `price`, `discount`, `total`) VALUES  ('"
            . $this->customer_id . "', '"
            . $this->my_number . "', '"
            . $this->size . "', '"
            . $this->brand . "', '"
            . $this->serial_no . "', '"
            . $this->dag_received_date . "', '"
            . $this->vehicle_number . "', '"
            . $this->remark . "', '"
            . ($this->price ?? 0) . "', '"
            . ($this->discount ?? 0) . "', '"
            . ($this->total ?? 0) . "')";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $last_id = mysqli_insert_id($db->DB_CON);
            
            // If dag_number is not provided, generate a new sequential one
            if ($dag_number === null) {
                $last_dag_query = "SELECT dag_number FROM `dag_customers` WHERE dag_number LIKE 'DAG-%' ORDER BY id DESC LIMIT 1";
                $last_dag_result = mysqli_fetch_assoc($db->readQuery($last_dag_query));
                $next_num = 1;
                if ($last_dag_result) {
                    $next_num = (int) str_replace('DAG-', '', $last_dag_result['dag_number']) + 1;
                }
                $dag_number = 'DAG-' . str_pad($next_num, 5, "0", STR_PAD_LEFT);
            }

            $update_query = "UPDATE `dag_customers` SET `dag_number` = '" . $dag_number . "' WHERE `id` = " . $last_id;
            $db->readQuery($update_query);

            $this->__construct($last_id);
            return $this;
        } else {
            return FALSE;
        }
    }

    public function all()
    {
        $query = "SELECT * FROM `dag_customers` ORDER BY id ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getGroupedAll()
    {
        $query = "SELECT dc.*, COUNT(dc.id) as item_count, GROUP_CONCAT(dc.my_number SEPARATOR ' | ') as all_my_numbers, 
                         c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code 
                  FROM `dag_customers` dc
                  JOIN `customer_master` c ON dc.customer_id = c.id
                  GROUP BY dc.dag_number 
                  ORDER BY dc.id DESC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getItemsByDagNumber($dag_number)
    {
        $query = "SELECT * FROM `dag_customers` WHERE `dag_number` = '" . $dag_number . "' ORDER BY id ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_array($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function update()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`customer_id` ='" . $this->customer_id . "', "
            . "`my_number` ='" . $this->my_number . "', "
            . "`size` ='" . $this->size . "', "
            . "`brand` ='" . $this->brand . "', "
            . "`serial_no` ='" . $this->serial_no . "', "
            . "`dag_received_date` ='" . $this->dag_received_date . "', "
            . "`vehicle_number` ='" . $this->vehicle_number . "', "
            . "`remark` ='" . $this->remark . "', "
            . "`price` ='" . ($this->price ?? 0) . "', "
            . "`discount` ='" . ($this->discount ?? 0) . "', "
            . "`total` ='" . ($this->total ?? 0) . "' "
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

    public function updateInvoice()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`cost` ='" . ($this->cost ?? 0) . "', "
            . "`price` ='" . ($this->price ?? 0) . "', "
            . "`discount` ='" . ($this->discount ?? 0) . "', "
            . "`total` ='" . ($this->total ?? 0) . "', "
            . "`is_invoiced` = 1, "
            . "`is_cancelled` = 0, "
            . "`issued_date` = " . ($this->issued_date ? "'" . $this->issued_date . "'" : "NULL") . " "
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

    public function clearInvoice()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`cost` = 0, "
            . "`price` = 0, "
            . "`discount` = 0, "
            . "`total` = 0, "
            . "`is_invoiced` = 0 "
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

    public function cancelInvoice()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`is_cancelled` = 1, "
            . "`is_invoiced` = 0, "
            . "`cost` = 0, "
            . "`price` = 0, "
            . "`discount` = 0, "
            . "`total` = 0 "
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
        $query = 'DELETE FROM `dag_customers` WHERE id="' . $this->id . '"';

        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    public function deleteByDagNumber($dag_number)
    {
        $query = "DELETE FROM `dag_customers` WHERE `dag_number` = '" . $dag_number . "'";
        $db = Database::getInstance();
        return $db->readQuery($query);
    }

    public function getNextId()
    {
        $db = Database::getInstance();
        $query = "SELECT dag_number FROM `dag_customers` WHERE dag_number LIKE 'DAG-%' ORDER BY id DESC LIMIT 1";
        $result = mysqli_fetch_assoc($db->readQuery($query));
        $next_num = 1;
        if ($result) {
            $next_num = (int) str_replace('DAG-', '', $result['dag_number']) + 1;
        }
        return $next_num;
    }

    public function getByCustomerId($customer_id, $invoiced = null)
    {
        $query = "SELECT dc.*, c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  WHERE dc.customer_id = " . (int) $customer_id;

        if ($invoiced === 0) {
            // For new invoice: show uninvoiced OR cancelled (can be re-invoiced)
            // Exclude only if ALL company assignments are rejected (no successful one exists)
            $query .= " AND (dc.is_invoiced = 0 OR dc.is_cancelled = 1)";
            $query .= " AND (
                NOT EXISTS (SELECT 1 FROM dag_company_assignment_items WHERE dag_id = dc.id)
                OR EXISTS (SELECT 1 FROM dag_company_assignment_items WHERE dag_id = dc.id AND (company_status IS NULL OR LOWER(company_status) NOT LIKE '%reject%'))
            )";
        } elseif ($invoiced === 1) {
            // For editing: show invoiced and NOT cancelled
            $query .= " AND dc.is_invoiced = 1 AND (dc.is_cancelled = 0 OR dc.is_cancelled IS NULL)";
        }

        $query .= " GROUP BY dc.id ORDER BY dc.id ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function searchForInvoice($keyword)
    {
        $db = Database::getInstance();
        $keyword = mysqli_real_escape_string($db->DB_CON, $keyword);
        $query = "SELECT dc.*, c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  WHERE dc.is_invoiced = 1
                    AND (dc.dag_number LIKE '%$keyword%' 
                     OR dc.my_number LIKE '%$keyword%' 
                     OR dc.serial_no LIKE '%$keyword%'
                     OR c.name LIKE '%$keyword%')
                  GROUP BY dc.id
                  ORDER BY dc.id DESC 
                  LIMIT 50";

        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getCustomersWithDags()
    {
        $query = "SELECT DISTINCT c.id, c.code, c.name, c.name_2 
                  FROM `customer_master` c 
                  INNER JOIN `dag_customers` dc ON c.id = dc.customer_id 
                  ORDER BY c.name ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $array_res = array();

        while ($row = mysqli_fetch_assoc($result)) {
            array_push($array_res, $row);
        }

        return $array_res;
    }

    public function getReportData($filters = [])
    {
        $query = "SELECT dc.id, dc.dag_number, dc.my_number, dc.size, dc.brand, dc.serial_no, 
                         dc.dag_received_date, dc.vehicle_number, dc.remark,
                         dc.is_invoiced, dc.is_cancelled,
                         c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name,
                         dca.assignment_number, dca.company_receipt_number, dca.company_issued_date,
                         dcai.job_number, dcai.belt_design, dcai.company_status, 
                         dcai.company_received_date, dcai.uc_number,
                         dii.cost, dii.price, dii.discount, dii.total, dii.issued_date,
                         di.invoice_number, di.payment_mode, di.is_cancelled as invoice_cancelled
                  FROM `dag_customers` dc
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  LEFT JOIN `dag_invoice_items` dii ON dii.dag_id = dc.id
                  LEFT JOIN `dag_invoices` di ON di.id = dii.invoice_id
                  WHERE 1=1";

        if (!empty($filters['date_from'])) {
            $query .= " AND dc.dag_received_date >= '" . $filters['date_from'] . "'";
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND dc.dag_received_date <= '" . $filters['date_to'] . "'";
        }
        if (!empty($filters['customer'])) {
            $query .= " AND (c.name LIKE '%" . $filters['customer'] . "%' OR c.code LIKE '%" . $filters['customer'] . "%')";
        }
        if (!empty($filters['company'])) {
            $query .= " AND cm.name LIKE '%" . $filters['company'] . "%'";
        }
        if (!empty($filters['brand'])) {
            $query .= " AND dc.brand = '" . $filters['brand'] . "'";
        }
        if (isset($filters['invoice_status']) && $filters['invoice_status'] !== '') {
            if ($filters['invoice_status'] === 'invoiced') {
                $query .= " AND dc.is_invoiced = 1 AND (dc.is_cancelled = 0 OR dc.is_cancelled IS NULL)";
            } elseif ($filters['invoice_status'] === 'not_invoiced') {
                $query .= " AND dc.is_invoiced = 0";
            } elseif ($filters['invoice_status'] === 'cancelled') {
                $query .= " AND dc.is_cancelled = 1";
            }
        }

        $query .= " ORDER BY dc.id DESC, dcai.id ASC";

        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $dags = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $dag_id = $row['id'];

            if (!isset($dags[$dag_id])) {
                $dags[$dag_id] = [
                    'id' => $row['id'],
                    'dag_number' => $row['dag_number'],
                    'my_number' => $row['my_number'],
                    'size' => $row['size'],
                    'brand' => $row['brand'],
                    'serial_no' => $row['serial_no'],
                    'dag_received_date' => $row['dag_received_date'],
                    'vehicle_number' => $row['vehicle_number'],
                    'remark' => $row['remark'],
                    'is_invoiced' => $row['is_invoiced'],
                    'is_cancelled' => $row['is_cancelled'],
                    'customer_name' => $row['customer_name'],
                    'customer_name_2' => $row['customer_name_2'],
                    'customer_code' => $row['customer_code'],
                    // Primary billing info (active only)
                    'cost' => $row['is_invoiced'] == 1 ? $row['cost'] : 0,
                    'price' => $row['is_invoiced'] == 1 ? $row['price'] : 0,
                    'discount' => $row['is_invoiced'] == 1 ? $row['discount'] : 0,
                    'total' => $row['is_invoiced'] == 1 ? $row['total'] : 0,
                    'issued_date' => $row['is_invoiced'] == 1 ? $row['issued_date'] : null,
                    'invoice_number' => $row['is_invoiced'] == 1 ? $row['invoice_number'] : null,
                    'invoice_cancelled' => 0, // Master row only shows active invoice
                    'payment_mode' => $row['is_invoiced'] == 1 ? $row['payment_mode'] : null,
                    'active_invoice_found' => ($row['is_invoiced'] == 1 && $row['invoice_cancelled'] == 0),
                    // History containers (indexed first to prevent duplicates)
                    'company_assignments_map' => [],
                    'invoice_history_map' => []
                ];
            }

            // Add company assignment if it exists (deduplicate by job number/assignment)
            if (!empty($row['company_name'])) {
                $assign_key = $row['assignment_number'] . '-' . $row['job_number'];
                if (!isset($dags[$dag_id]['company_assignments_map'][$assign_key])) {
                    $dags[$dag_id]['company_assignments_map'][$assign_key] = [
                        'company_name' => $row['company_name'],
                        'assignment_number' => $row['assignment_number'],
                        'company_receipt_number' => $row['company_receipt_number'],
                        'company_issued_date' => $row['company_issued_date'],
                        'job_number' => $row['job_number'],
                        'belt_design' => $row['belt_design'],
                        'company_status' => $row['company_status'],
                        'company_received_date' => $row['company_received_date'],
                        'uc_number' => $row['uc_number']
                    ];
                }
            }

            // Add invoice to history if it exists (deduplicate by invoice number)
            if (!empty($row['invoice_number'])) {
                $inv_num = $row['invoice_number'];
                if (!isset($dags[$dag_id]['invoice_history_map'][$inv_num])) {
                    $dags[$dag_id]['invoice_history_map'][$inv_num] = [
                        'invoice_number' => $row['invoice_number'],
                        'cost' => $row['cost'] ?? 0,
                        'price' => $row['price'] ?? 0,
                        'discount' => $row['discount'] ?? 0,
                        'total' => $row['total'] ?? 0,
                        'issued_date' => $row['issued_date'] ?? null,
                        'invoice_cancelled' => $row['invoice_cancelled'] ?? 0,
                        'payment_mode' => $row['payment_mode'] ?? null
                    ];

                    // If we find an ACTIVE invoice, ensure it is the one shown in the master row
                    if ($row['invoice_cancelled'] == 0 && $row['is_invoiced'] == 1) {
                        $dags[$dag_id]['cost'] = $row['cost'];
                        $dags[$dag_id]['price'] = $row['price'];
                        $dags[$dag_id]['discount'] = $row['discount'];
                        $dags[$dag_id]['total'] = $row['total'];
                        $dags[$dag_id]['issued_date'] = $row['issued_date'];
                        $dags[$dag_id]['invoice_number'] = $row['invoice_number'];
                        $dags[$dag_id]['payment_mode'] = $row['payment_mode'];
                        $dags[$dag_id]['active_invoice_found'] = true;
                    }
                }
            }
        }

        // Finalize arrays
        foreach ($dags as &$dag) {
            $dag['company_assignments'] = array_values($dag['company_assignments_map']);
            $dag['invoice_history'] = array_values($dag['invoice_history_map']);
            unset($dag['company_assignments_map'], $dag['invoice_history_map']);
        }

        return array_values($dags);
    }

    public function getByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        if (is_array($ids)) {
            $ids_str = implode(',', array_map('intval', $ids));
        } else {
            $ids_str = $ids;
        }

        $query = "SELECT dc.*, c.name as customer_name, c.name_2 as customer_name_2, c.code as customer_code,
                         cm.name as company_name,
                         dca.assignment_number, dca.company_receipt_number, dca.company_issued_date,
                         dcai.job_number, dcai.belt_design, dcai.company_status, 
                         dcai.company_received_date, dcai.uc_number
                  FROM `dag_customers` dc 
                  LEFT JOIN `customer_master` c ON dc.customer_id = c.id 
                  LEFT JOIN `dag_company_assignment_items` dcai ON dcai.dag_id = dc.id
                  LEFT JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  LEFT JOIN `company_master` cm ON cm.id = dca.company_id
                  WHERE dc.id IN ($ids_str)
                  ORDER BY dc.id ASC";

        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $dags = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $dag_id = $row['id'];
            if (!isset($dags[$dag_id])) {
                $dags[$dag_id] = [
                    'id' => $row['id'],
                    'dag_number' => $row['dag_number'],
                    'my_number' => $row['my_number'],
                    'size' => $row['size'],
                    'brand' => $row['brand'],
                    'serial_no' => $row['serial_no'],
                    'dag_received_date' => $row['dag_received_date'],
                    'cost' => $row['cost'],
                    'price' => $row['price'],
                    'discount' => $row['discount'],
                    'total' => $row['total'],
                    'is_invoiced' => $row['is_invoiced'],
                    'is_cancelled' => $row['is_cancelled'],
                    'issued_date' => $row['issued_date'],
                    'customer_id' => $row['customer_id'],
                    'customer_name' => $row['customer_name'],
                    'customer_name_2' => $row['customer_name_2'],
                    'customer_code' => $row['customer_code'],
                    'company_name' => $row['company_name']
                ];
            }
        }

        return array_values($dags);
    }

    public function getDistinctCompanies()
    {
        $query = "SELECT DISTINCT cm.name 
                  FROM `dag_company_assignment_items` dcai
                  JOIN `dag_company_assignments` dca ON dca.id = dcai.assignment_id
                  JOIN `company_master` cm ON cm.id = dca.company_id
                  ORDER BY cm.name ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $companies = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $companies[] = $row['name'];
        }
        return $companies;
    }

    public function getDistinctBrands()
    {
        $query = "SELECT DISTINCT TRIM(brand) as brand FROM `dag_customers` WHERE brand IS NOT NULL AND TRIM(brand) != '' ORDER BY brand ASC";
        $db = Database::getInstance();
        $result = $db->readQuery($query);
        $brands = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $brands[] = $row['brand'];
        }
        return $brands;
    }

}
