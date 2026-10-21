<?php

class ArnItem
{
    public $id;
    public $arn_id;
    public $item_code;
    public $order_qty;
    public $received_qty;
    public $discount_1;
    public $discount_2;
    public $discount_3;
    public $discount_4;
    public $discount_5;
    public $discount_6;
    public $discount_7;
    public $discount_8;
    public $final_cost;
    public $unit_total;
    public $list_price;
    public $invoice_price;
    public $margin_percent;
    public $created_at;
    public $updated_at;
    public $is_cancelled;
    public $price_without_vat;
    public $price_with_vat;

    private static $vatColumnsChecked = false;

    public static function ensureVatColumns()
    {
        if (self::$vatColumnsChecked) {
            return;
        }

        self::$vatColumnsChecked = true;
        $db = Database::getInstance();
        $result = $db->readQuery("SHOW COLUMNS FROM `arn_items` LIKE 'price_with_vat'");

        if ($result && mysqli_num_rows($result) > 0) {
            return;
        }

        @mysqli_query($db->DB_CON, "ALTER TABLE `arn_items` ADD COLUMN `price_without_vat` DECIMAL(15,2) NOT NULL DEFAULT 0, ADD COLUMN `price_with_vat` DECIMAL(15,2) NOT NULL DEFAULT 0");
    }

    public function __construct($id = NULL)
    {
        self::ensureVatColumns();
        if ($id) {
            $db = Database::getInstance();
            $query = "SELECT * FROM `arn_items` WHERE `id` = '$id'";
            $result = $db->readQuery($query);
            if ($row = mysqli_fetch_assoc($result)) {
                $this->id = $row['id'];
                $this->arn_id = $row['arn_id'];
                $this->item_code = $row['item_code'];
                $this->order_qty = $row['order_qty'];
                $this->received_qty = $row['received_qty'];
                $this->discount_1 = $row['discount_1'];
                $this->discount_2 = $row['discount_2'];
                $this->discount_3 = $row['discount_3'];
                $this->discount_4 = $row['discount_4'];
                $this->discount_5 = $row['discount_5'];
                $this->discount_6 = $row['discount_6'];
                $this->discount_7 = $row['discount_7'];
                $this->discount_8 = $row['discount_8'];
                $this->final_cost = $row['final_cost'];
                $this->unit_total = $row['unit_total'];
                $this->list_price = $row['list_price'];
                $this->invoice_price = $row['invoice_price'];
                $this->created_at = $row['created_at'];
                $this->updated_at = $row['updated_at'];
                $this->is_cancelled = $row['is_cancelled'];
                $this->price_without_vat = $row['price_without_vat'];
                $this->price_with_vat = $row['price_with_vat'];
            }
        }
    }

    public function create()
    {
        $db = Database::getInstance();
        $query = "INSERT INTO `arn_items` (
            `arn_id`, `item_code`, `order_qty`, `received_qty`,
            `discount_1`, `discount_2`, `discount_3`, `discount_4`, `discount_5`, `discount_6`, `discount_7`, `discount_8`, `final_cost`, `unit_total`,
            `list_price`,`invoice_price`, `price_without_vat`, `price_with_vat`,  `created_at`
        ) VALUES (
            '{$this->arn_id}', '{$this->item_code}', '{$this->order_qty}', '{$this->received_qty}',
            '{$this->discount_1}', '{$this->discount_2}', '{$this->discount_3}', '{$this->discount_4}', '{$this->discount_5}', '{$this->discount_6}', '{$this->discount_7}', '{$this->discount_8}', '{$this->final_cost}', '{$this->unit_total}',
            '{$this->list_price}', '{$this->invoice_price}', '" . (float)$this->price_without_vat . "', '" . (float)$this->price_with_vat . "',  NOW()
        )";


        $result = $db->readQuery($query);
        if ($result) {
            return mysqli_insert_id($db->DB_CON);
        } else {
            return false;
        }
    }

    public static function all()
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM `arn_items`";
        $result = $db->readQuery($query);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        return $items;
    }

    public function delete()
    {
        $db = Database::getInstance();
        $query = "DELETE FROM `arn_items` WHERE `id` = '{$this->id}'";
        return $db->readQuery($query);
    }

    public function update()
    {
        $db = Database::getInstance();
        $query = "UPDATE `arn_items` SET 
            `order_qty` = '{$this->order_qty}',
            `received_qty` = '{$this->received_qty}',
            `discount_1` = '{$this->discount_1}',
            `discount_2` = '{$this->discount_2}',
            `discount_3` = '{$this->discount_3}',
            `discount_4` = '{$this->discount_4}',
            `discount_5` = '{$this->discount_5}',
            `discount_6` = '{$this->discount_6}',
            `discount_7` = '{$this->discount_7}',
            `discount_8` = '{$this->discount_8}',
            `final_cost` = '{$this->final_cost}',
            `unit_total` = '{$this->unit_total}',
            `list_price` = '{$this->list_price}',
            `invoice_price` = '{$this->invoice_price}', 
            `margin_percent` = '{$this->margin_percent}',
            `updated_at` = NOW()
        WHERE `id` = '{$this->id}'";

        return $db->readQuery($query);
    }

    public function getArnCostByArnId($arn_id)
    {
        $db = Database::getInstance();
        $query = "SELECT `final_cost` FROM `arn_items` WHERE `arn_id` = '{$arn_id}'";
        $result = $db->readQuery($query);

        if ($row = mysqli_fetch_assoc($result)) {
            return $row['final_cost'];
        }
        
        return false;
    }
}
