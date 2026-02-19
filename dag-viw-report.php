<?php
include 'class/include.php';
include 'auth.php';


$status = '';
$dag_no = '';
$my_number = '';
$belt_id = '';
$size_id = '';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DAG View Report |
        <?php echo $COMPANY_PROFILE_DETAILS->name ?>
    </title>
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
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-2 align-items-end">

                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
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
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">DAG Number</label>
                                            <input type="text" class="form-control" name="dag_no" id="dag_no"
                                                placeholder="Search DAG..." autocomplete="off">
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
                                            <label class="form-label">My Number</label>
                                            <input type="text" class="form-control" name="my_number" id="my_number"
                                                placeholder="Search My No..." autocomplete="off">
                                        </div>
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
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
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6">
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
                                        <div class="col-xxl-2 col-xl-3 col-lg-4 col-sm-6 ms-auto">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-primary w-100" id="btn-filter">
                                                    <i class="uil uil-filter me-1"></i> Filter
                                                </button>
                                                <button type="button" class="btn btn-secondary w-100"
                                                    id="btn-reset-filter">
                                                    <i class="uil uil-redo me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Filter Section -->

                    <!-- Report Table -->
                    <style>
                        #dag-report-table td.details-control {
                            cursor: pointer;
                            text-align: center;
                        }

                        #dag-report-table tr.shown {
                            background-color: #f0f4ff !important;
                        }

                        .dag-items-table th {
                            background-color: #e9ecef;
                            font-size: 13px;
                        }

                        .dag-items-table td {
                            font-size: 13px;
                        }
                    </style>
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
                                            class="table table-bordered dt-responsive nowrap w-100"
                                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:30px;"></th>
                                                    <th>Ref No</th>
                                                    <th>Company Issued Date</th>
                                                    <th>Company</th>
                                                    <th>Items</th>
                                                    <th>Total Amount</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data loaded via AJAX DataTables -->
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