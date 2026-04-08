<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$dag_number = isset($_GET['dag_number']) ? $_GET['dag_number'] : '';

$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);

$DAG_CUSTOMER = new DagCustomer(NULL);
$items = [];

if ($dag_number) {
    $items = $DAG_CUSTOMER->getItemsByDagNumber($dag_number);
    if (!empty($items)) {
        // Use the first item to populate common details
        $DAG_CUSTOMER->__construct($items[0]['id']);
    }
} elseif ($id) {
    $DAG_CUSTOMER->__construct($id);
    $items = [$DAG_CUSTOMER->all()[0]]; // This is a bit hacky, but DagCustomer->all() returns everything. 
    // Wait, DagCustomer doesn't have a good way to get JUST this one item as an array row easily without re-querying or using constructor properties.
    // Let's just manually create the array for the loop if only ID is provided.
    $items = [[
        'id' => $DAG_CUSTOMER->id,
        'size' => $DAG_CUSTOMER->size,
        'brand' => $DAG_CUSTOMER->brand,
        'serial_no' => $DAG_CUSTOMER->serial_no,
        'dag_received_date' => $DAG_CUSTOMER->dag_received_date
    ]];
}

$CUSTOMER = new CustomerMaster($DAG_CUSTOMER->customer_id);
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DAG Customer Print |
        <?php echo $COMPANY_PROFILE->name ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- include main CSS -->
    <?php include 'main-css.php' ?>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm;
                margin-top: 20mm;
            }

            body {
                width: 100%;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .container,
            .container-fluid {
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }

        .table th,
        .table td {
            padding: 6px 10px !important;
            vertical-align: middle;
            border: 1px solid #dee2e6;
            font-size: 13px;
        }

        .table thead th {
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 600;
            font-size: 12px;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .company-logo {
            max-height: 100px;
            width: auto;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4>DAG Customer Print</h4>
            <div>
                <button onclick="window.print()" class="btn btn-success ms-2">
                    <i class="mdi mdi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Header Section -->
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <img src="./uploads/company-logos/<?php echo $COMPANY_PROFILE->image_name ?>"
                            class="company-logo" alt="logo">
                        <div class="text-muted mt-2">
                            <p class="mb-1"><i class="uil uil-building me-1"></i>
                                <?php echo $COMPANY_PROFILE->name ?>
                            </p>
                            <p class="mb-1"><i class="uil uil-map-marker me-1"></i>
                                <?php echo $COMPANY_PROFILE->address ?>
                            </p>
                            <p><i class="uil uil-phone me-1"></i>
                                <?php echo $COMPANY_PROFILE->mobile_number_1 ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h4 class="font-size-16 mb-2">BILL NO:
                            #<?php echo htmlspecialchars($DAG_CUSTOMER->getDagNumber() ? $DAG_CUSTOMER->getDagNumber() : 'DAG-' . str_pad($DAG_CUSTOMER->id, 5, "0", STR_PAD_LEFT)); ?>
                        </h4>
                        <p class="mb-1"><strong>Print Date:</strong>
                            <?php echo date('d M, Y'); ?>
                        </p>
                        <p class="mb-0"><strong>Customer Name:</strong>
                            <?php echo htmlspecialchars($CUSTOMER->name . (isset($CUSTOMER->name_2) ? ' ' . $CUSTOMER->name_2 : '')); ?>
                        </p>
                    </div>
                </div>

                <!-- DAG Customer Details -->
                <h6 class="section-title">DAG Information</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-centered table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Size</th>
                                <th>Brand</th>
                                <th>My Number</th>
                                <th>Serial No</th>
                                <th>Received Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item) { ?>
                                <tr>
                                    <td><?php echo str_pad($index + 1, 2, "0", STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                                    <td><?php echo htmlspecialchars($item['brand']); ?></td>
                                    <td><?php echo htmlspecialchars($item['my_number']); ?></td>
                                    <td><?php echo htmlspecialchars($item['serial_no']); ?></td>
                                    <td><?php echo htmlspecialchars($item['dag_received_date']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Signature Section -->
                <div style="margin-top: 60px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Prepared By</strong>
                            </td>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Checked By</strong>
                            </td>
                            <td style="text-align: center;">
                                _________________________<br>
                                <strong>Approved By</strong>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                window.print();
            }
        });
    </script>
</body>

</html>