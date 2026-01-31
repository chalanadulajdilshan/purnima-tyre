<?php
include 'class/include.php';
include 'auth.php';

$DAG_REPORT = new Dag();

// Get filter parameters
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$dag_no = isset($_GET['dag_no']) ? $_GET['dag_no'] : '';
$my_number = isset($_GET['my_number']) ? $_GET['my_number'] : '';
$belt_id = isset($_GET['belt_id']) ? $_GET['belt_id'] : '';
$size_id = isset($_GET['size_id']) ? $_GET['size_id'] : '';

// Get filtered DAG reports
$reports = $DAG_REPORT->getFilteredReports($from_date, $to_date, $status, $dag_no, $my_number, $belt_id, $size_id);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DAG View Report | <?php echo $COMPANY_PROFILE_DETAILS->name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name ?>" name="author" />

    <!-- Include main CSS -->
    <?php include 'main-css.php' ?>

    <!-- DataTables CSS -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
        type="text/css" />
</head>

<body data-layout="horizontal" data-topbar="colored" class="someBlock">
    <div id="layout-wrapper">
        <?php include 'navigation.php' ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 font-size-18">DAG View Report</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reports</a></li>
                                        <li class="breadcrumb-item active">DAG View Report</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End page title -->

                    <!-- Filter Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Filter Options</h4>
                                    <form id="filter-form" method="get" action="">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-2">
                                                <label for="from_date" class="form-label">From Date</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control date-picker" id="from_date"
                                                        name="from_date" value="<?php echo $from_date ?>">
                                                    <span class="input-group-text"><i
                                                            class="mdi mdi-calendar"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <label for="to_date" class="form-label">To Date</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control date-picker" id="to_date"
                                                        name="to_date" value="<?php echo $to_date ?>">
                                                    <span class="input-group-text"><i
                                                            class="mdi mdi-calendar"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="filter_status" id="filter_status">
                                                    <option value="">All Status</option>
                                                    <?php
                                                    $DAG_ITEM = new DagItem();
                                                    $statuses = $DAG_ITEM->getDistinctStatuses();
                                                    foreach ($statuses as $st) {
                                                        $selected = ($status == $st) ? 'selected' : '';
                                                        echo '<option value="' . $st . '" ' . $selected . '>' . ucfirst($st) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">DAG Number</label>
                                                <input type="text" class="form-control" name="dag_no" id="dag_no"
                                                    value="<?php echo htmlspecialchars($dag_no) ?>"
                                                    placeholder="Search DAG...">
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">My Number</label>
                                                <input type="text" class="form-control" name="my_number" id="my_number"
                                                    value="<?php echo htmlspecialchars($my_number ?? '') ?>"
                                                    placeholder="Search My No...">
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Belt</label>
                                                <select class="form-select" name="belt_id" id="belt_id">
                                                    <option value="">All Belts</option>
                                                    <?php
                                                    $BELT = new BeltMaster();
                                                    $belts = $BELT->all();
                                                    foreach ($belts as $belt) {
                                                        $selected = (isset($belt_id) && $belt_id == $belt['id']) ? 'selected' : '';
                                                        echo '<option value="' . $belt['id'] . '" ' . $selected . '>' . htmlspecialchars($belt['name']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label">Size</label>
                                                <select class="form-select" name="size_id" id="size_id">
                                                    <option value="">All Sizes</option>
                                                    <?php
                                                    $SIZE = new Sizes();
                                                    $sizes = $SIZE->all();
                                                    foreach ($sizes as $size) {
                                                        $selected = (isset($size_id) && $size_id == $size['id']) ? 'selected' : '';
                                                        echo '<option value="' . $size['id'] . '" ' . $selected . '>' . htmlspecialchars($size['name']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-primary w-100" id="btn-filter">
                                                        <i class="uil uil-filter me-1"></i> Filter
                                                    </button>
                                                    <button class="btn btn-secondary w-100" id="btn-reset-filter">
                                                        <i class="uil uil-redo me-1"></i> Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Filter Section -->

                    <!-- Report Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-4">
                                        <h4 class="card-title">DAG Report</h4>
                                        <div>
                                            <button class="btn btn-danger btn-sm" onclick="printReport()">
                                                <i class="mdi mdi-printer me-1"></i> Print
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="dag-report-table"
                                            class="table table-bordered dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Ref No</th>
                                                    <th>My Number</th>
                                                    <th>Dag Received Date</th>
                                                    <th>Customer</th>
                                                    <th>Company</th>
                                                    <th>Department</th>
                                                    <th>Belt Design</th>
                                                    <th>Serial No</th>
                                                    <th>Vehicle No</th>
                                                    <th>Customer Issue Date</th>
                                                    <th>Total Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($reports)): ?>
                                                    <?php $counter = 1; ?>
                                                    <?php foreach ($reports as $report): ?>
                                                        <tr>
                                                            <td><?php echo $counter++; ?></td>
                                                            <td><?php echo htmlspecialchars($report['ref_no']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['my_number']); ?></td>
                                                            <td><?php echo date('d/m/Y', strtotime($report['received_date'])); ?>
                                                            </td>
                                                            <td>
                                                                <?php echo htmlspecialchars($report['customer_name']); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($report['company_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['department_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['belt_design']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['serial_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($report['vehicle_no']); ?></td>
                                                            <td>
                                                                <?php
                                                                $c_date = $report['customer_issue_date'];
                                                                if (!empty($c_date) && $c_date != '0000-00-00') {
                                                                    echo date('d/m/Y', strtotime($c_date));
                                                                } else {
                                                                    echo '<span class="text-danger">Not Issued</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <?php echo number_format($report['total_amount'], 2); ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $status = isset($report['status']) ? $report['status'] : 'Pending';
                                                                $status_class = '';
                                                                switch (strtolower($status)) {
                                                                    case 'received':
                                                                        $status_class = 'bg-success';
                                                                        break;
                                                                    case 'assigned':
                                                                        $status_class = 'bg-primary';
                                                                        break;
                                                                    case 'approved':
                                                                        $status_class = 'bg-info';
                                                                        break;
                                                                    case 'rejected_company':
                                                                        $status_class = 'bg-danger';
                                                                        break;
                                                                    case 'pending':
                                                                    default:
                                                                        $status_class = 'bg-warning';
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $status_class; ?> font-size-12">
                                                                    <?php echo ucfirst($status); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="dag-receipt-print.php?id=<?php echo $report['id']; ?>"
                                                                    target="_blank" class="btn btn-info btn-sm">
                                                                    <i class="mdi mdi-printer"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="12" class="text-center">No records found</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Report Table -->
                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'footer.php' ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <?php include 'main-js.php' ?>

    <!-- Required datatable js -->
    <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- DAG Report JS -->
    <script src="ajax/js/dag-viw-report.js"></script>

</body>

</html>