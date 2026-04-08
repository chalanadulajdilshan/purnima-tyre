<?php
include 'class/include.php';
$db = Database::getInstance();
$query = "ALTER TABLE `dag_customers` ADD `vehicle_number` VARCHAR(100) NULL AFTER `remark` ";
$result = $db->readQuery($query);
if ($result) {
    echo "Column 'vehicle_number' added successfully.";
} else {
    echo "Error adding column.";
}
?>
