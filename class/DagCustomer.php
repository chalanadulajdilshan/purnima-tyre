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
    public $remark;
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
                $this->remark = $result['remark'];
                $this->created_at = $result['created_at'];
                $this->updated_at = $result['updated_at'];
            }
        }
    }

    public function create()
    {
        $query = "INSERT INTO `dag_customers` (`customer_id`, `my_number`, `size`, `brand`, `serial_no`, `dag_received_date`, `remark`) VALUES  ('"
            . $this->customer_id . "', '"
            . $this->my_number . "', '"
            . $this->size . "', '"
            . $this->brand . "', '"
            . $this->serial_no . "', '"
            . $this->dag_received_date . "', '"
            . $this->remark . "')";

        $db = Database::getInstance();
        $result = $db->readQuery($query);

        if ($result) {
            $last_id = mysqli_insert_id($db->DB_CON);
            $dag_number = 'DAG-' . str_pad($last_id, 5, "0", STR_PAD_LEFT);

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

    public function update()
    {
        $query = "UPDATE `dag_customers` SET "
            . "`customer_id` ='" . $this->customer_id . "', "
            . "`my_number` ='" . $this->my_number . "', "
            . "`size` ='" . $this->size . "', "
            . "`brand` ='" . $this->brand . "', "
            . "`serial_no` ='" . $this->serial_no . "', "
            . "`dag_received_date` ='" . $this->dag_received_date . "', "
            . "`remark` ='" . $this->remark . "' "
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

    public function getNextId()
    {
        $query = "SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dag_customers'";
        $db = Database::getInstance();
        $result = mysqli_fetch_assoc($db->readQuery($query));
        return $result ? $result['AUTO_INCREMENT'] : 1;
    }

}
