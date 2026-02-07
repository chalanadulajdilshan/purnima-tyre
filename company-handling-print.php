<!doctype html>
<?php
include 'class/include.php';

if (!isset($_SESSION)) {
    session_start();
}

// Validate ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid ID');
}

$handling_id = (int) $_GET['id'];
$US = new User($_SESSION['id']);
$COMPANY_PROFILE = new CompanyProfile($US->company_id);
$COMPANY_HANDLING = new CompanyHandling($handling_id);

// Verify record exists
if (!$COMPANY_HANDLING->id) {
    die('Record not found');
}

// Get complaint details
$COMPLAINT = null;
if ($COMPANY_HANDLING->complaint_id) {
    $db = Database::getInstance();
    $query = "SELECT cc.*, cm.name as customer_name, cm.mobile_number as customer_mobile, cm.address as customer_address
              FROM `customer_complaint` cc
              LEFT JOIN `customer_master` cm ON cc.customer_id = cm.id
              WHERE cc.id = " . (int) $COMPANY_HANDLING->complaint_id;
    $result = $db->readQuery($query);
    if ($result) {
        $COMPLAINT = mysqli_fetch_assoc($result);
    }
}
?>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Bill | <?php echo $COMPANY_PROFILE->name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'main-css.php' ?>
    <link href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" rel="stylesheet">

    <style>
        @media print {

            /* Hide non-print elements */
            .no-print {
                display: none !important;
            }

            /* Make invoice full width */
            body,
            html {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            #invoice-content,
            .card {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }

        /* Table styling with proper padding */
        #invoice-content .table-items {
            width: 100%;
            border-collapse: collapse;
        }

        #invoice-content .table-items th {
            padding: 10px 15px !important;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
            font-weight: 600;
        }

        #invoice-content .table-items td {
            padding: 10px 15px !important;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            vertical-align: middle;
        }

        #invoice-content .table-items .total-row td {
            border-bottom: none;
            padding: 8px 15px !important;
        }

        #invoice-content .table-items .signature-row td {
            padding-top: 40px !important;
            border-bottom: none;
        }
    </style>

</head>

<body data-layout="horizontal" data-topbar="colored">

    <div class="container mt-4">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 no-print gap-2">
            <h4 class="mb-0">Bill</h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button onclick="window.print()" class="btn btn-success ms-2">Print</button>
                <button onclick="downloadPDF()" class="btn btn-primary ms-2">PDF</button>
            </div>
        </div>

        <div class="card" id="invoice-content">
            <div class="card-body" style="padding: 25px;">
                <!-- Company & Customer Info -->
                <div class="invoice-title">
                    <div class="row mb-4">
                        <?php
                        function formatPhone($number)
                        {
                            $number = preg_replace('/\D/', '', $number);
                            if (strlen($number) == 10) {
                                return sprintf("(%s) %s-%s", substr($number, 0, 3), substr($number, 3, 3), substr($number, 6));
                            }
                            return $number;
                        }
                        ?>
                        <div class="col-md-5 text-muted">
                            <p class="mb-1" style="font-weight:bold;font-size:18px;">
                                <?php echo $COMPANY_PROFILE->name ?>
                            </p>
                            <p class="mb-1" style="font-size:13px;"><?php echo $COMPANY_PROFILE->address ?></p>
                            <p class="mb-1" style="font-size:13px;">
                                <?php echo formatPhone($COMPANY_PROFILE->mobile_number_1); ?>
                                <?php echo $COMPANY_PROFILE->email ?>
                            </p>
                            <p class="mb-1" style="font-size:13px;">VAT Registration No:
                                <?php echo $COMPANY_PROFILE->vat_number ?><br>
                            </p>
                        </div>
                        <div class="col-md-4 text-sm-start text-md-start">
                            <h3 style="font-weight:bold;font-size:18px;">BILL</h3>
                            <p class="mb-1 text-muted" style="font-size:14px;"><strong>Customer Name:</strong>
                                <?php echo htmlspecialchars($COMPLAINT['customer_name'] ?? ''); ?></p>
                            <p class="mb-1 text-muted" style="font-size:14px;"><strong>Customer Mobile:</strong>
                                <?php echo !empty($COMPLAINT['customer_address']) ? htmlspecialchars($COMPLAINT['customer_address']) : '' ?>
                                -
                                <?php echo !empty($COMPLAINT['customer_mobile']) ? htmlspecialchars($COMPLAINT['customer_mobile']) : '.................................'; ?>
                            </p>
                        </div>

                        <div class="col-md-3 text-sm-start text-md-end">
                            <p class="mb-1" style="font-size:14px;"><strong>Ref No:</strong>
                                <?php echo htmlspecialchars($COMPLAINT['complaint_no'] ?? 'N/A'); ?></p>
                            <p class="mb-1" style="font-size:14px;"><strong>UC No:</strong>
                                <?php echo htmlspecialchars($COMPLAINT['uc_number'] ?? 'N/A'); ?></p>
                            <p class="mb-1" style="font-size:14px;"><strong>Date:</strong>
                                <?php echo date('d M, Y'); ?></p>
                        </div>
                    </div>

                    <!-- ITEM TABLE -->
                    <div class="table-responsive">
                        <table class="table-items">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No.</th>
                                    <th>Description</th>
                                    <th style="width: 150px;">Company</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 120px; text-align: right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>01</td>
                                    <td><?php echo htmlspecialchars($COMPLAINT['fault_description'] ?? 'Service Charge'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($COMPANY_HANDLING->company_name ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($COMPANY_HANDLING->company_status ?: 'N/A'); ?></td>
                                    <td style="text-align: right;">
                                        <?php echo $COMPANY_HANDLING->price_amount ? number_format($COMPANY_HANDLING->price_amount, 2) : '0.00'; ?>
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td colspan="3" rowspan="3"
                                        style="vertical-align: top; padding-top: 15px !important;">
                                        <h6 style="margin-bottom: 8px;"><strong>Terms & Conditions:</strong></h6>
                                        <ul style="padding-left: 20px; margin-bottom: 0; color: #666;">
                                            <li>Payment is due upon receipt</li>
                                            <li>Please retain this bill for your records</li>
                                        </ul>
                                    </td>
                                    <td style="text-align: right;"><strong>Sub Total:</strong></td>
                                    <td style="text-align: right;">
                                        <strong><?php echo $COMPANY_HANDLING->price_amount ? number_format($COMPANY_HANDLING->price_amount, 2) : '0.00'; ?></strong>
                                    </td>
                                </tr>
                                <tr class="total-row">
                                    <td style="text-align: right;"><strong>VAT:</strong></td>
                                    <td style="text-align: right;"><strong>0.00</strong></td>
                                </tr>
                                <tr class="total-row">
                                    <td style="text-align: right; border-top: 1px solid #333;"><strong>Net
                                            Amount:</strong></td>
                                    <td style="text-align: right; border-top: 1px solid #333;">
                                        <strong><?php echo $COMPANY_HANDLING->price_amount ? number_format($COMPANY_HANDLING->price_amount, 2) : '0.00'; ?></strong>
                                    </td>
                                </tr>
                                <tr class="signature-row">
                                    <td colspan="5">
                                        <table style="width: 100%; margin-top: 30px;">
                                            <tr>
                                                <td style="text-align: center; width: 33%;">
                                                    <div
                                                        style="border-top: 1px solid #333; width: 150px; margin: 0 auto; padding-top: 8px;">
                                                        <strong>Prepared By</strong>
                                                    </div>
                                                </td>
                                                <td style="text-align: center; width: 33%;">
                                                    <div
                                                        style="border-top: 1px solid #333; width: 150px; margin: 0 auto; padding-top: 8px;">
                                                        <strong>Approved By</strong>
                                                    </div>
                                                </td>
                                                <td style="text-align: center; width: 33%;">
                                                    <div
                                                        style="border-top: 1px solid #333; width: 150px; margin: 0 auto; padding-top: 8px;">
                                                        <strong>Received By</strong>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice-content');
            const opt = {
                margin: 0.5,
                filename: 'Bill_<?php echo $COMPLAINT['complaint_no'] ?? $handling_id; ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().set(opt).from(element).save();
        }

        // Trigger print on Enter
        document.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                window.print();
            }
        });
    </script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>