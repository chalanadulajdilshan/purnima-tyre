jQuery(document).ready(function () {

    let selectedDags = []; // Track added DAG IDs to prevent duplicates
    let selectedCustomerId = null;

    // ==========================================
    // CUSTOMER SEARCH MODAL
    // ==========================================
    $("#searchCustomerBtn").click(function () {
        let keyword = $("#customerSearchInput").val();

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { search_customer: true, keyword: keyword },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach((customer, index) => {
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${customer.code}</td>
                                <td>${customer.full_name}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info select-customer-btn" 
                                        data-id="${customer.id}" 
                                        data-code="${customer.code}"
                                        data-name="${customer.full_name}">
                                        Select
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="4" class="text-center">No customers found.</td></tr>`;
                    }
                    $("#customerSelectionTableBody").html(html);
                }
            }
        });
    });

    // Auto-search when modal opens
    $('#customerSearchModal').on('show.bs.modal', function () {
        $("#customerSearchInput").val('');
        $("#searchCustomerBtn").trigger('click');
    });

    // Handle customer selection (ONLY sets customer — does NOT load items)
    $(document).on("click", ".select-customer-btn", function () {
        selectedCustomerId = $(this).data("id");
        let customerCode = $(this).data("code");
        let customerName = $(this).data("name");

        $("#customer_code").val(customerCode);
        $("#customer_name").val(customerName);
        $("#customer_id").val(selectedCustomerId);

        $("#customerSearchModal").modal("hide");
    });

    // ==========================================
    // DAG ITEM SEARCH MODAL
    // ==========================================
    $("#searchDagItemBtn").click(function () {
        let keyword = $("#dagItemSearchInput").val();

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { search_dag_items: true, keyword: keyword },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach((dag, index) => {
                            let dagNumber = dag.dag_number || 'DAG-' + String(dag.id).padStart(5, '0');
                            let customerName = dag.customer_full_name || '-';
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${dagNumber}</td>
                                <td>${dag.my_number}</td>
                                <td>${customerName}</td>
                                <td>${dag.size}</td>
                                <td>${dag.brand}</td>
                                <td>${dag.serial_no}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info select-dag-item-btn" 
                                        data-id="${dag.id}" 
                                        data-dagnumber="${dagNumber}"
                                        data-mynumber="${dag.my_number}"
                                        data-customer="${customerName}"
                                        data-size="${dag.size}"
                                        data-brand="${dag.brand}"
                                        data-serialno="${dag.serial_no}"
                                        data-company="${dag.company_name || '-'}"
                                        data-cost="${dag.cost || 0}">
                                        Select
                                    </button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="8" class="text-center">No matching DAG items found.</td></tr>`;
                    }
                    $("#dagItemSelectionTableBody").html(html);
                }
            }
        });
    });

    // Auto-search when modal opens
    $('#dagItemSearchModal').on('show.bs.modal', function () {
        $("#dagItemSearchInput").val('');
        $("#searchDagItemBtn").trigger('click');
    });

    // Handle DAG item selection — add to invoice table
    $(document).on("click", ".select-dag-item-btn", function () {
        let dagId = parseInt($(this).data("id"));

        // Duplicate check (ensure numeric comparison)
        if (selectedDags.includes(dagId)) {
            swal({ title: "Warning!", text: "This DAG item is already added.", type: "warning", timer: 2000, showConfirmButton: false });
            return;
        }

        selectedDags.push(dagId);

        let dagNumber = $(this).data("dagnumber");
        let myNumber = $(this).data("mynumber");
        let company = $(this).data("company");
        let size = $(this).data("size");
        let brand = $(this).data("brand");
        let serialNo = $(this).data("serialno");
        let cost = parseFloat($(this).data("cost")) || 0;

        let rowCount = $("#dagInvoiceItemsBody tr").length + 1;
        let rowHtml = `<tr data-dag-id="${dagId}">
            <td class="row-number">${rowCount}</td>
            <td>${dagNumber}</td>
            <td>${myNumber}</td>
            <td>${company}</td>
            <td>${size}</td>
            <td>${brand}</td>
            <td>${serialNo}</td>
            <td><input type="text" class="form-control form-control-sm item-issued-date" value="" placeholder="Select" readonly></td>
            <td><input type="number" class="form-control form-control-sm item-cost" value="${cost.toFixed(2)}" step="0.01" min="0"></td>
            <td><input type="number" class="form-control form-control-sm item-price" value="0.00" step="0.01" min="0"></td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control form-control-sm item-discount" value="0.00" step="0.01" min="0" max="100">
                    <span class="input-group-text">%</span>
                </div>
            </td>
            <td class="item-total fw-bold">0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-dag-row" title="Remove"><i class="uil uil-times"></i></button></td>
        </tr>`;

        $("#dagInvoiceItemsBody").append(rowHtml);

        // Initialize datepicker on the new row
        $(".item-issued-date").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        $("#dagItemSearchModal").modal("hide");
        calculateTotals();
    });

    // ==========================================
    // REMOVE DAG ROW
    // ==========================================
    $(document).on("click", ".remove-dag-row", function () {
        let dagId = $(this).closest("tr").data("dag-id");
        selectedDags = selectedDags.filter(id => id !== dagId);
        $(this).closest("tr").remove();
        updateRowNumbers();
        calculateTotals();
    });

    function updateRowNumbers() {
        $("#dagInvoiceItemsBody tr").each(function (i) {
            $(this).find(".row-number").text(i + 1);
        });
    }

    // ==========================================
    // CALCULATE ITEM TOTAL
    // ==========================================
    $(document).on("input", ".item-price, .item-discount", function () {
        let row = $(this).closest("tr");
        let price = parseFloat(row.find(".item-price").val()) || 0;
        let discountPct = parseFloat(row.find(".item-discount").val()) || 0;
        if (discountPct > 100) discountPct = 100;
        if (discountPct < 0) discountPct = 0;

        let discountAmount = price * discountPct / 100;
        let total = price - discountAmount;
        if (total < 0) total = 0;
        row.find(".item-total").text(total.toFixed(2));
        calculateTotals();
    });

    // ==========================================
    // CALCULATE GRAND TOTALS
    // ==========================================
    function calculateTotals() {
        let subTotal = 0;
        let discountTotal = 0;
        let grandTotal = 0;

        $("#dagInvoiceItemsBody tr").each(function () {
            let price = parseFloat($(this).find(".item-price").val()) || 0;
            let discountPct = parseFloat($(this).find(".item-discount").val()) || 0;
            let discountAmount = price * discountPct / 100;
            let total = price - discountAmount;
            if (total < 0) total = 0;

            subTotal += price;
            discountTotal += discountAmount;
            grandTotal += total;
        });

        $("#subTotal").val(subTotal.toFixed(2));
        $("#disTotal").val(discountTotal.toFixed(2));
        $("#grandTotal").val(grandTotal.toFixed(2));
    }

    // ==========================================
    // SAVE INVOICE
    // ==========================================
    $("#save").click(function (e) {
        e.preventDefault();
        saveOrUpdate("create");
    });

    // ==========================================
    // UPDATE INVOICE
    // ==========================================
    $("#update").click(function (e) {
        e.preventDefault();
        saveOrUpdate("update");
    });

    function saveOrUpdate(actionType) {
        if (!$("#customer_id").val()) {
            swal({ title: "Error!", text: "Please select a customer first.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        let items = [];
        let hasItems = false;

        $("#dagInvoiceItemsBody tr").each(function () {
            let dagId = $(this).data("dag-id");
            if (dagId) {
                hasItems = true;
                let price = parseFloat($(this).find(".item-price").val()) || 0;
                let discountPct = parseFloat($(this).find(".item-discount").val()) || 0;
                let discountAmount = price * discountPct / 100;
                let total = price - discountAmount;
                if (total < 0) total = 0;

                items.push({
                    dag_id: dagId,
                    cost: parseFloat($(this).find(".item-cost").val()) || 0,
                    price: price,
                    discount: discountPct,
                    total: total,
                    issued_date: $(this).find(".item-issued-date").val() || ''
                });
            }
        });

        if (!hasItems) {
            swal({ title: "Error!", text: "Please add at least one DAG item.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        $(".someBlock").preloader();

        let postData = {
            customer_id: $("#customer_id").val(),
            payment_mode: $("input[name='payment_mode']:checked").val(),
            items: JSON.stringify(items)
        };
        postData[actionType] = true;

        if (actionType === "update") {
            postData.id = $("#invoice_id").val();
        }

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: postData,
            dataType: "json",
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: actionType === "create" ? "Invoice Saved Successfully" : "Invoice Updated Successfully",
                        type: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                } else {
                    swal({
                        title: "Error!",
                        text: result.message || "Error saving data",
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                $(".someBlock").preloader("remove");
                swal({ title: "Error!", text: "Server error occurred.", type: "error", timer: 2000, showConfirmButton: false });
            }
        });
    }

    // ==========================================
    // INVOICE SEARCH MODAL
    // ==========================================
    $("#searchInvoiceBtn").click(function () {
        let keyword = $("#invoiceSearchInput").val();

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { search_invoice: true, keyword: keyword },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach((inv, index) => {
                            let cancelledBadge = parseInt(inv.is_cancelled) === 1 ? ' <span class="badge bg-danger">Cancelled</span>' : '';
                            let actionButtons = '';
                            if (parseInt(inv.is_cancelled) !== 1) {
                                actionButtons = `
                                    <button type="button" class="btn btn-sm btn-info load-invoice-btn me-1" 
                                        data-id="${inv.id}" title="Edit">
                                        <i class="uil uil-edit"></i>
                                    </button>
                                    <a href="print-dag-invoice.php?invoice_id=${inv.id}" 
                                       target="_blank" class="btn btn-sm btn-secondary" title="Print">
                                        <i class="uil uil-print"></i>
                                    </a>`;
                            }
                            html += `<tr class="${parseInt(inv.is_cancelled) === 1 ? 'table-secondary' : ''}">
                                <td>${index + 1}</td>
                                <td>${inv.invoice_number}${cancelledBadge}</td>
                                <td>${inv.customer_full_name}</td>
                                <td>${inv.item_count}</td>
                                <td><span class="badge ${inv.payment_mode === 'credit' ? 'bg-warning' : 'bg-success'}">${inv.payment_mode}</span></td>
                                <td class="text-end">${parseFloat(inv.grand_total).toFixed(2)}</td>
                                <td>${inv.invoice_date || '-'}</td>
                                <td>${actionButtons}</td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="8" class="text-center">No invoices found.</td></tr>`;
                    }
                    $("#invoiceSelectionTableBody").html(html);
                }
            }
        });
    });

    // Auto-search when modal opens
    $('#invoiceSearchModal').on('show.bs.modal', function () {
        $("#invoiceSearchInput").val('');
        $("#searchInvoiceBtn").trigger('click');
    });

    // Load existing invoice for editing
    $(document).on("click", ".load-invoice-btn", function () {
        let invoiceId = $(this).data("id");

        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { get_invoice_items: true, invoice_id: invoiceId },
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    let invoice = response.invoice;

                    // Populate header
                    $("#invoice_id").val(invoice.id);
                    $("#invoice_number").val(invoice.invoice_number);
                    $("#customer_id").val(invoice.customer_id);
                    selectedCustomerId = invoice.customer_id;

                    // Set payment mode
                    if (invoice.payment_mode === 'credit') {
                        $("#pay_credit").prop("checked", true);
                    } else {
                        $("#pay_cash").prop("checked", true);
                    }

                    // Populate customer name from first item
                    if (response.data.length > 0) {
                        let firstItem = response.data[0];
                        let fullName = (firstItem.customer_name || '') + ' ' + (firstItem.customer_name_2 || '');
                        $("#customer_code").val(firstItem.customer_code || '');
                        $("#customer_name").val(fullName.trim());
                    }

                    // Populate items
                    $("#dagInvoiceItemsBody").empty();
                    selectedDags = [];

                    response.data.forEach((item, index) => {
                        selectedDags.push(parseInt(item.dag_id));
                        let dagNumber = item.dag_number || 'DAG-' + String(item.dag_id).padStart(5, '0');
                        let cost = parseFloat(item.cost) || 0;
                        let price = parseFloat(item.price) || 0;
                        let discount = parseFloat(item.discount) || 0;
                        let total = parseFloat(item.total) || 0;
                        let companyName = item.company_name || '-';

                        let rowHtml = `<tr data-dag-id="${item.dag_id}">
                            <td class="row-number">${index + 1}</td>
                            <td>${dagNumber}</td>
                            <td>${item.my_number || ''}</td>
                            <td>${companyName}</td>
                            <td>${item.size || ''}</td>
                            <td>${item.brand || ''}</td>
                            <td>${item.serial_no || ''}</td>
                            <td><input type="text" class="form-control form-control-sm item-issued-date" value="${item.issued_date || ''}" placeholder="Select" readonly></td>
                            <td><input type="number" class="form-control form-control-sm item-cost" value="${cost.toFixed(2)}" step="0.01" min="0"></td>
                            <td><input type="number" class="form-control form-control-sm item-price" value="${price.toFixed(2)}" step="0.01" min="0"></td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm item-discount" value="${discount.toFixed(2)}" step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </td>
                            <td class="item-total fw-bold">${total.toFixed(2)}</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-dag-row" title="Remove"><i class="uil uil-times"></i></button></td>
                        </tr>`;

                        $("#dagInvoiceItemsBody").append(rowHtml);
                    });

                    // Initialize datepickers
                    $(".item-issued-date").datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });

                    // Toggle buttons
                    $("#save").hide();
                    $("#update").show();
                    $("#deleteInvoice").show();
                    $("#cancelInvoice").show();
                    $("#print").show().attr("href", "print-dag-invoice.php?invoice_id=" + invoice.id);

                    calculateTotals();
                }
            }
        });

        $("#invoiceSearchModal").modal("hide");
    });

    // ==========================================
    // DELETE INVOICE
    // ==========================================
    $("#deleteInvoice").click(function (e) {
        e.preventDefault();

        let invoiceId = $("#invoice_id").val();
        if (!invoiceId || invoiceId === "0") {
            swal({ title: "Error!", text: "No invoice selected.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        swal({
            title: "Are you sure?",
            text: "This will delete the invoice and un-mark all associated DAG items.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }, function (isConfirm) {
            if (isConfirm) {
                $(".someBlock").preloader();

                $.ajax({
                    url: "ajax/php/dag-invoice.php",
                    type: "POST",
                    data: { delete: true, id: invoiceId },
                    dataType: "json",
                    success: function (result) {
                        $(".someBlock").preloader("remove");
                        if (result.status === 'success') {
                            swal({
                                title: "Deleted!",
                                text: "Invoice deleted successfully.",
                                type: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            swal({ title: "Error!", text: result.message || "Failed to delete.", type: "error", timer: 2000, showConfirmButton: false });
                        }
                    },
                    error: function () {
                        $(".someBlock").preloader("remove");
                        swal({ title: "Error!", text: "Server error.", type: "error", timer: 2000, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // ==========================================
    // CANCEL INVOICE
    // ==========================================
    $("#cancelInvoice").click(function (e) {
        e.preventDefault();

        let invoiceId = $("#invoice_id").val();
        if (!invoiceId || invoiceId === "0") {
            swal({ title: "Error!", text: "No invoice selected.", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        swal({
            title: "Cancel Invoice?",
            text: "This will cancel the invoice. The data will be preserved for records.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Yes, cancel it!"
        }, function (isConfirm) {
            if (isConfirm) {
                $(".someBlock").preloader();

                $.ajax({
                    url: "ajax/php/dag-invoice.php",
                    type: "POST",
                    data: { cancel_invoice: true, id: invoiceId },
                    dataType: "json",
                    success: function (result) {
                        $(".someBlock").preloader("remove");
                        if (result.status === 'success') {
                            swal({
                                title: "Cancelled!",
                                text: "Invoice has been cancelled.",
                                type: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        } else {
                            swal({ title: "Error!", text: result.message || "Failed to cancel.", type: "error", timer: 2000, showConfirmButton: false });
                        }
                    },
                    error: function () {
                        $(".someBlock").preloader("remove");
                        swal({ title: "Error!", text: "Server error.", type: "error", timer: 2000, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // ==========================================
    // NEW BUTTON RESET
    // ==========================================
    $('#new').click(function (e) {
        e.preventDefault();
        selectedCustomerId = null;
        selectedDags = [];
        $("#form-data")[0].reset();
        $("#invoice_id").val("0");
        $("#customer_code").val('');
        $("#customer_name").val('');
        $("#customer_id").val('');
        $("#dagInvoiceItemsBody").empty();
        $("#subTotal").val('0.00');
        $("#disTotal").val('0.00');
        $("#grandTotal").val('0.00');
        $("#save").show();
        $("#update").hide();
        $("#deleteInvoice").hide();
        $("#print").hide();
        $("#cancelInvoice").hide();

        // Fetch next invoice number
        $.ajax({
            url: "ajax/php/dag-invoice.php",
            type: "POST",
            data: { get_next_id: true },
            dataType: "json",
            success: function (result) {
                if (result.status === 'success') {
                    $("#invoice_number").val(result.next_id);
                }
            }
        });
    });

});
