jQuery(document).ready(function () {

    // ==========================================
    // GENERATE ROWS BASED ON QTY
    // ==========================================
    $("#generateRowsBtn").click(function () {
        if (!$("#customer_id").val()) {
            swal({ title: "Error!", text: "Please select a Customer first", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }
        if (!$("#dag_received_date").val()) {
            swal({ title: "Error!", text: "Please select DAG Received Date", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        let qty = parseInt($("#item_qty").val()) || 0;
        if (qty < 1) {
            swal({ title: "Error!", text: "Please enter a valid quantity (min 1)", type: "error", timer: 2000, showConfirmButton: false });
            return;
        }

        // Clear existing rows
        $("#dagItemsBody").empty();

        // Generate rows
        for (let i = 0; i < qty; i++) {
            addNewRow();
        }

        // Show items section
        $("#dagItemsSection").addClass("show");
        updateRowCount();
    });

    // ==========================================
    // ADD SINGLE ROW
    // ==========================================
    $("#addOneRowBtn").click(function () {
        addNewRow();
        updateRowCount();
    });

    function addNewRow(data) {
        let rowCount = $("#dagItemsBody tr").length + 1;
        data = data || {};

        // Build size options
        let sizeOptionsHtml = '<option value="">--Select--</option>';
        if (typeof sizeOptions !== 'undefined') {
            sizeOptions.forEach(function (size) {
                let selected = (data.size && data.size === size) ? 'selected' : '';
                sizeOptionsHtml += `<option value="${size}" ${selected}>${size}</option>`;
            });
        }

        // Build brand options
        let brandOptionsHtml = '<option value="">--Select--</option>';
        if (typeof brandOptions !== 'undefined') {
            brandOptions.forEach(function (brand) {
                let selected = (data.brand && data.brand === brand) ? 'selected' : '';
                brandOptionsHtml += `<option value="${brand}" ${selected}>${brand}</option>`;
            });
        }

        let rowHtml = `<tr>
            <td class="row-number text-center">${rowCount}</td>
            <td><input type="text" class="form-control item-my-number" placeholder="Enter My Number" value="${data.my_number || ''}"></td>
            <td>
                <select class="form-select item-size">
                    ${sizeOptionsHtml}
                </select>
            </td>
            <td>
                <select class="form-select item-brand">
                    ${brandOptionsHtml}
                </select>
            </td>
            <td><input type="text" class="form-control item-serial-no" placeholder="Enter Serial No" value="${data.serial_no || ''}"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove">
                    <i class="uil uil-times"></i>
                </button>
            </td>
        </tr>`;

        $("#dagItemsBody").append(rowHtml);
    }

    // ==========================================
    // REMOVE ROW
    // ==========================================
    $(document).on("click", ".remove-row-btn", function () {
        $(this).closest("tr").remove();
        updateRowNumbers();
        updateRowCount();

        // Hide section if no rows
        if ($("#dagItemsBody tr").length === 0) {
            $("#dagItemsSection").removeClass("show");
        }
    });

    function updateRowNumbers() {
        $("#dagItemsBody tr").each(function (index) {
            $(this).find(".row-number").text(index + 1);
        });
    }

    function updateRowCount() {
        let count = $("#dagItemsBody tr").length;
        $("#rowCountText").text(count + (count === 1 ? ' item' : ' items'));
    }

    // ==========================================
    // VALIDATE ALL ROWS
    // ==========================================
    function validateForm() {
        if (!$("#customer_id").val()) {
            swal({ title: "Error!", text: "Please select Customer", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }
        if (!$("#dag_received_date").val()) {
            swal({ title: "Error!", text: "Please select DAG Received Date", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }

        let rowCount = $("#dagItemsBody tr").length;
        if (rowCount === 0) {
            swal({ title: "Error!", text: "Please add at least one DAG item", type: "error", timer: 2000, showConfirmButton: false });
            return false;
        }

        let isValid = true;
        let errorMsg = '';

        $("#dagItemsBody tr").each(function (index) {
            let rowNum = index + 1;
            let myNumber = $(this).find(".item-my-number").val().trim();
            let size = $(this).find(".item-size").val();
            let brand = $(this).find(".item-brand").val();
            let serialNo = $(this).find(".item-serial-no").val().trim();

            if (!myNumber) {
                errorMsg = "Row " + rowNum + ": Please enter My Number";
                isValid = false;
                return false;
            }
            if (!size) {
                errorMsg = "Row " + rowNum + ": Please select Size";
                isValid = false;
                return false;
            }
            if (!brand) {
                errorMsg = "Row " + rowNum + ": Please select Brand";
                isValid = false;
                return false;
            }
            if (!serialNo) {
                errorMsg = "Row " + rowNum + ": Please enter Serial No";
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            swal({ title: "Error!", text: errorMsg, type: "error", timer: 3000, showConfirmButton: false });
        }

        return isValid;
    }

    // ==========================================
    // CREATE (SAVE) - Multiple Items
    // ==========================================
    $("#create").click(function (event) {
        event.preventDefault();

        if (!validateForm()) {
            return false;
        }

        // Collect items from rows
        let items = [];
        $("#dagItemsBody tr").each(function () {
            items.push({
                my_number: $(this).find(".item-my-number").val().trim(),
                size: $(this).find(".item-size").val(),
                brand: $(this).find(".item-brand").val(),
                serial_no: $(this).find(".item-serial-no").val().trim()
            });
        });

        // Preloader start
        $(".someBlock").preloader();

        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: {
                create_multiple: true,
                customer_id: $("#customer_id").val(),
                dag_received_date: $("#dag_received_date").val(),
                vehicle_number: $("#vehicle_number").val(),
                remark: $("#remark").val(),
                items: JSON.stringify(items)
            },
            dataType: 'json',
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: result.message || "DAG Customer Details Saved Successfully",
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
    });

    // ==========================================
    // UPDATE - Single Item (from search modal)
    // ==========================================
    $("#update").click(function (event) {
        event.preventDefault();

        if (!validateForm()) {
            return false;
        }

        // For update, we use the first (and only) row
        let firstRow = $("#dagItemsBody tr").first();

        // Preloader start
        $(".someBlock").preloader();

        var formData = new FormData($("#form-data")[0]);
        formData.append("update", true);
        formData.append("my_number", firstRow.find(".item-my-number").val().trim());
        formData.append("size", firstRow.find(".item-size").val());
        formData.append("brand", firstRow.find(".item-brand").val());
        formData.append("serial_no", firstRow.find(".item-serial-no").val().trim());
        formData.append("vehicle_number", $("#vehicle_number").val());

        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: formData,
            async: false,
            dataType: 'json',
            success: function (result) {
                $(".someBlock").preloader("remove");
                if (result.status === 'success') {
                    swal({
                        title: "Success!",
                        text: "DAG Customer Details Updated Successfully",
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
                        text: "Error updating data",
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    // ==========================================
    // DELETE
    // ==========================================
    $(document).on("click", ".delete-item", function (e) {
        e.preventDefault();

        var id = $("#id").val();
        var dag_number = $("#dag_number").val();

        if (!id || id === "0") {
            swal({
                title: "Error!",
                text: "Please select a record first.",
                type: "error",
                timer: 2000,
                showConfirmButton: false,
            });
            return;
        }

        swal(
            {
                title: "Are you sure?",
                text: "Do you want to delete the complete DAG" + (dag_number ? ": " + dag_number : "") + "?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false,
            },
            function (isConfirm) {
                if (isConfirm) {
                    $(".someBlock").preloader();

                    $.ajax({
                        url: "ajax/php/dag-customer.php",
                        type: "POST",
                        data: {
                            id: id,
                            dag_number: dag_number,
                            delete: true,
                        },
                        dataType: "json",
                        success: function (response) {
                            $(".someBlock").preloader("remove");

                            if (response.status === "success") {
                                swal({
                                    title: "Deleted!",
                                    text: "Record has been deleted.",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false,
                                });

                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Something went wrong.",
                                    type: "error",
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                            }
                        },
                    });
                }
            }
        );
    });

    // ==========================================
    // NEW BUTTON - Reset
    // ==========================================
    $('#new').click(function (e) {
        e.preventDefault();
        $("#form-data")[0].reset();
        setNextDagNumber();
        $("#id").val("0");
        $("#dag_number").val("");
        $("#customer_id").val("");
        $("#item_qty").val("1");
        $("#dagItemsBody").empty();
        $("#dagItemsSection").removeClass("show");
        updateRowCount();

        $("#create").show();
        $("#update").hide();
        $(".delete-item").hide();
        $("#print").hide();
    });

    // ==========================================
    // INITIALIZE MODAL TABLE WITH EXPANSION
    // ==========================================
    var modalTable = $('#dagCustomerTable').DataTable({
        responsive: true,
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 8] },
            { visible: false, targets: [6] }
        ]
    });

    // Toggle expansion
    $('#dagCustomerTable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = modalTable.row(tr);
        var icon = $(this).find('i');
        var dag_number = tr.data('dag_number');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('mdi-minus-circle-outline').addClass('mdi-plus-circle-outline');
        } else {
            // Fetch items for this DAG
            $.ajax({
                url: "ajax/php/dag-customer.php",
                type: "POST",
                data: {
                    get_dag_items: true,
                    dag_number: dag_number
                },
                dataType: 'json',
                success: function (result) {
                    if (result.status === 'success') {
                        let innerHtml = '<div class="p-3 bg-light"><table class="table table-sm table-bordered mb-0 bg-white">';
                        innerHtml += '<thead class="table-dark"><tr><th>My #</th><th>Size</th><th>Brand</th><th>Serial No</th></tr></thead><tbody>';
                        result.data.forEach(function (item) {
                            innerHtml += `<tr><td>${item.my_number}</td><td>${item.size}</td><td>${item.brand}</td><td>${item.serial_no}</td></tr>`;
                        });
                        innerHtml += '</tbody></table></div>';
                        row.child(innerHtml).show();
                        tr.addClass('shown');
                        icon.removeClass('mdi-plus-circle-outline').addClass('mdi-minus-circle-outline');
                    }
                }
            });
        }
    });

    // ==========================================
    // SELECT GROUPED DAG
    // ==========================================
    $(document).on("click", ".select-dag-group", function () {
        var tr = $(this).closest('tr');
        var dag_number = tr.data("dag_number");

        $("#customer_id").val(tr.data("customer_id"));
        $("#customer_code").val(tr.data("customer_code"));
        $("#customer_name").val(tr.data("customer_name"));
        $("#dag_received_date").val(tr.data("dag_received_date"));
        $("#vehicle_number").val(tr.data("vehicle_number"));
        $("#remark").val(tr.data("remark"));
        $("#dag_number").val(dag_number);
        $("#dag_number_display").val(dag_number);

        // Fetch all items for this DAG
        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: {
                get_dag_items: true,
                dag_number: dag_number
            },
            dataType: 'json',
            success: function (result) {
                if (result.status === 'success') {
                    // Clear and populate rows
                    $("#dagItemsBody").empty();
                    result.data.forEach(function (item) {
                        addNewRow(item);
                    });

                    // Show items section
                    $("#dagItemsSection").addClass("show");
                    updateRowCount();

                    // Note: Update mode for batch is complex, so for now we just load them.
                    // If you want to update, you might need a batch-update endpoint.
                    // For now, let's just allow editing the rows.
                    
                    // We'll hide "Create" and show "Update" if it's a single item, 
                    // or maybe we should disable individual update for batches for now?
                    // Let's just switch to "Update" mode if at least one item was loaded.
                    if (result.data.length > 0) {
                        $("#id").val(result.data[0].id); // Set first ID as reference if needed
                        $("#create").hide();
                        $("#update").show();
                        $(".delete-item").show();
                        $("#print").show();
                        $("#print").attr("href", "print-dag-customer.php?dag_number=" + dag_number);
                    }

                    $("#dagCustomerModal").modal("hide");
                }
            }
        });
    });

    // ==========================================
    // FETCH NEXT DAG NUMBER
    // ==========================================
    function setNextDagNumber() {
        $.ajax({
            url: "ajax/php/dag-customer.php",
            type: "POST",
            data: { get_next_id: true },
            dataType: 'json',
            success: function (result) {
                if (result.status === 'success') {
                    $("#dag_number_display").val(result.next_id);
                }
            }
        });
    }

    // Set on load
    setNextDagNumber();
});
