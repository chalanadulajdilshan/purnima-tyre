<!doctype html>
<?php
include 'class/include.php';
include 'auth.php';

$DAG_INVOICE = new DagInvoice(NULL);
$nextId = $DAG_INVOICE->getNextId();
$invoice_number = 'DINV-' . str_pad($nextId, 5, "0", STR_PAD_LEFT);
?>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>DAG Invoice |
        <?php echo $COMPANY_PROFILE_DETAILS->name ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="<?php echo $COMPANY_PROFILE_DETAILS->name ?>" name="author" />
    <!-- include main CSS -->
    <?php include 'main-css.php' ?>

</head>

<body data-layout="horizontal" data-topbar="colored" class="someBlock">

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'navigation.php' ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-md-8 d-flex align-items-center flex-wrap gap-2">
                            <a href="#" class="btn btn-success" id="new">
                                <i class="uil uil-plus me-1"></i> New
                            </a>

                            <?php if ($PERMISSIONS['add_page'] ?? true): ?>
                                <a href="#" class="btn btn-primary" id="save">
                                    <i class="uil uil-save me-1"></i> Save
                                </a>
                            <?php endif; ?>

                            <?php if ($PERMISSIONS['edit_page'] ?? true): ?>
                                <a href="#" class="btn btn-warning" id="update" style="display: none;">
                                    <i class="uil uil-edit me-1"></i> Update
                                </a>
                            <?php endif; ?>

                            <?php if ($PERMISSIONS['delete_page'] ?? true): ?>
                                <a href="#" class="btn btn-danger" id="deleteInvoice" style="display: none;">
                                    <i class="uil uil-trash-alt me-1"></i> Delete
                                </a>
                            <?php endif; ?>

                            <a href="#" class="btn btn-secondary" id="print" target="_blank" style="display: none;">
                                <i class="uil uil-print me-1"></i> Print
                            </a>

                            <a href="#" class="btn btn-outline-danger" id="cancelInvoice" style="display: none;">
                                <i class="uil uil-times-circle me-1"></i> Cancel Invoice
                            </a>
                        </div>

                        <div class="col-md-4 text-md-end text-start mt-3 mt-md-0">
                            <ol class="breadcrumb m-0 justify-content-md-end">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">DAG Invoice</li>
                            </ol>
                        </div>
                    </div>

                    <!-- end page title -->

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    01
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="font-size-16 mb-1">Step 1: Invoice Details</h5>
                                            <p class="text-muted text-truncate mb-0">Set invoice number, customer, and
                                                payment mode</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <button class="btn btn-outline-info btn-sm" type="button"
                                                data-bs-toggle="modal" data-bs-target="#invoiceSearchModal">
                                                <i class="uil uil-search me-1"></i> Search Invoice
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 pt-0">
                                    <form id="form-data" autocomplete="off">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <label class="form-label" for="invoice_number">Invoice Number</label>
                                                <input id="invoice_number" name="invoice_number" type="text"
                                                    value="<?php echo $invoice_number; ?>"
                                                    class="form-control mb-3" readonly>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label" for="customer_code">Customer Code
                                                    <span class="text-danger">*</span></label>
                                                <div class="input-group mb-3">
                                                    <input id="customer_code" name="customer_code" type="text"
                                                        class="form-control" placeholder="Customer Code" readonly>
                                                    <button class="btn btn-info" type="button" data-bs-toggle="modal"
                                                        data-bs-target="#customerSearchModal">
                                                        <i class="uil uil-search"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label for="customer_name" class="form-label">Customer Name
                                                    <span class="text-danger">*</span></label>
                                                <input id="customer_name" name="customer_name" type="text"
                                                    class="form-control mb-3" placeholder="Select Customer" readonly>
                                            </div>

                                            <div class="col-md-5">
                                                <label class="form-label">Payment Mode</label>
                                                <div class="d-flex gap-3 mt-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input payment-mode" type="radio"
                                                            name="payment_mode" id="pay_cash" value="cash" checked>
                                                        <label class="form-check-label" for="pay_cash">Cash</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input payment-mode" type="radio"
                                                            name="payment_mode" id="pay_credit" value="credit">
                                                        <label class="form-check-label"
                                                            for="pay_credit">Credit</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" id="customer_id" name="customer_id">
                                            <input type="hidden" id="invoice_id" name="id" value="0">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DAG ITEMS SECTION -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    02
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <h5 class="font-size-16 mb-1">Step 2: Add DAG Items</h5>
                                            <p class="text-muted text-truncate mb-0">Search DAGs by My Number and add
                                                them to the invoice</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 pt-0">
                                    <div class="row mb-4 align-items-end">
                                        <div class="col-md-5">
                                            <label class="form-label" for="search_dag">Find DAG by Search</label>
                                            <button class="btn btn-secondary w-100" type="button"
                                                data-bs-toggle="modal" data-bs-target="#dagItemSearchModal">
                                                <i class="uil uil-search"></i> Select DAG Items
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" id="dagInvoiceTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="3%">#</th>
                                                    <th width="8%">DAG Number</th>
                                                    <th width="7%">My Number</th>
                                                    <th width="8%">Company</th>
                                                    <th width="6%">Size</th>
                                                    <th width="7%">Brand</th>
                                                    <th width="7%">Serial No</th>
                                                    <th width="10%">Issued Date</th>
                                                    <th width="9%">Cost</th>
                                                    <th width="9%">Price</th>
                                                    <th width="8%">Discount (%)</th>
                                                    <th width="9%">Total</th>
                                                    <th width="4%"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="dagInvoiceItemsBody">
                                                <!-- Items will dynamically appear here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Totals -->
                                    <hr class="my-4">
                                    <div class="row">
                                        <div class="col-md-5"></div>
                                        <div class="col-md-3"></div>
                                        <div class="col-md-4 mb-4">
                                            <div class="p-2 border rounded bg-light" style="max-width: 600px;">
                                                <div class="row mb-2">
                                                    <div class="col-7">
                                                        <input type="text" class="form-control text_purchase3"
                                                            value="Sub Total" disabled>
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" class="form-control" id="subTotal"
                                                            value="0.00" disabled>
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <div class="col-7">
                                                        <input type="text" class="form-control text_purchase3"
                                                            value="Discount Total:" disabled>
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" class="form-control" id="disTotal"
                                                            value="0.00" disabled>
                                                    </div>
                                                </div>

                                                <div class="row mb-2">
                                                    <div class="col-7">
                                                        <input type="text" class="form-control text_purchase3 fw-bold"
                                                            value="Grand Total:" disabled>
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" class="form-control fw-bold" id="grandTotal"
                                                            value="0.00" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- container-fluid -->
            </div>
            <?php include 'footer.php' ?>

        </div>
    </div>

    <!-- Customer Search Modal -->
    <div class="modal fade" id="customerSearchModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Select Customer</h5>
                    <div class="input-group ms-3" style="max-width: 400px;">
                        <input type="text" id="customerSearchInput" class="form-control"
                            placeholder="Search by Name or Code">
                        <button class="btn btn-outline-primary" type="button" id="searchCustomerBtn">
                            Search
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="customerSelectionTable"
                        class="table table-bordered table-hover dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Customer Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="customerSelectionTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DAG Item Search Modal -->
    <div class="modal fade" id="dagItemSearchModal" tabindex="-1" role="dialog" aria-labelledby="dagItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dagItemModalLabel">Select DAG Item</h5>
                    <div class="input-group ms-3" style="max-width: 400px;">
                        <input type="text" id="dagItemSearchInput" class="form-control"
                            placeholder="Search by My Number / Serial No / DAG No">
                        <button class="btn btn-outline-primary" type="button" id="searchDagItemBtn">
                            Search
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="dagItemSelectionTable"
                        class="table table-bordered table-hover dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>DAG Number</th>
                                <th>My Number</th>
                                <th>Customer</th>
                                <th>Size</th>
                                <th>Brand</th>
                                <th>Serial No</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="dagItemSelectionTableBody">
                            <!-- DAGs loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Search Modal -->
    <div class="modal fade" id="invoiceSearchModal" tabindex="-1" role="dialog"
        aria-labelledby="invoiceSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceSearchModalLabel">DAG Invoices</h5>
                    <div class="input-group ms-3" style="max-width: 400px;">
                        <input type="text" id="invoiceSearchInput" class="form-control"
                            placeholder="Search by Invoice No / Customer">
                        <button class="btn btn-outline-primary" type="button" id="searchInvoiceBtn">
                            Search
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table id="invoiceSelectionTable"
                        class="table table-bordered table-hover dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Payment</th>
                                <th class="text-end">Total</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceSelectionTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- include main js  -->
    <?php include 'main-js.php' ?>

    <!-- JAVASCRIPT -->
    <script src="ajax/js/common.js"></script>
    <script src="ajax/js/dag-invoice.js"></script>

</body>

</html>