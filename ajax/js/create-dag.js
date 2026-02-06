jQuery(document).ready(function () {

  function loadDagItemsToTable(items) {
    $("#dagItemsBodyInvoice").empty();

    if (!items.length) {
      $("#dagItemsBodyInvoice").append(`
      <tr id="noDagItemRow">
        <td colspan="6" class="text-center text-muted">No items found</td>
      </tr>`);
      return;
    }

    items.forEach((item) => {
      const price = parseFloat(item.price) || 0;
      const qty = parseFloat(item.qty) || 0;
      const total = price * qty;

      const row = $(`
    <tr class="dag-item-row clickable-row">
      <td>
        ${item.vehicle_no}
        <input type="hidden" class="vehicle_no" value="${item.vehicle_no}">
      </td>
      <td>
        ${item.belt_title}
        <input type="hidden" class="belt_id" value="${item.belt_id}">
      </td>
      <td>
        ${item.barcode}
        <input type="hidden" class="barcode" value="${item.barcode}">
      </td>
      <td>
        ${qty}
        <input type="hidden" class="qty" value="${qty}">
      </td>
      <td>
        <input type="number" class="form-control form-control-sm price" value="${price}" readonly>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm total_amount" value="${total.toFixed(2)}" readonly>
      </td>
    </tr>
    `);

      // On row click → populate input fields
      row.on("click", function () {
        $("#vehicleNo").val(item.vehicle_no);
        $("#beltDesign").val(item.belt_id).trigger("change");
        $("#barcode").val(item.barcode);
        $("#quantity").val(qty);
        $("#casingCost").val(price);
        $("#vehicleNo").focus();
      });

      $("#dagItemsBodyInvoice").append(row);
    });
  }


  function resetDagInputs() {
    $("#beltDesign").val("").trigger("change");
    $("#sizeDesign").val("").trigger("change");
    $("#brand_id").val("").trigger("change");
    $("#serial_num1").val("");
    $("#customer_code").val("");
    $("#customer_name").val("");
    $("#customer_id").val("");
    $("#uc").val("");
    // Reset new item-level fields
    $("#my_number").val("");
    $("#received_date").val("");
    $("#customer_issue_date").val("");
    $("#customer_request_date").val("");
    $("#vehicle_no").val("");
    $("#job_number").val("");
    $("#dag_status").val("pending").trigger("change");
    // Reset company delivery date
    $("#company_delivery_date").val("");
  }

  function resetDagForm() {
    // Reset all form inputs
    $("#form-data")[0].reset();

    // Reset select2 dropdowns
    $("#customer_id, #dag_company_id, #brand_id").val("").trigger("change");

    // Reset date inputs
    $("#received_date, #delivery_date, #customer_request_date, #company_issued_date, #company_delivery_date, #customer_issue_date").val("");

    // Reset my_number field
    $("#my_number").val("");

    // Reset status to default
    $("#status").val("pending");

    // Hide update button, show create button
    $("#update").hide();
    $("#create").show();

    // Hide print button
    $("#print").hide();

    // Reset hidden fields
    $("#id").val("0");
    $("#dag_id").val("");

    // Clear any error messages
    $(".text-danger").remove();
  }


  function addDagItem() {
    try {
      const beltDesignId = $("#beltDesign").val();
      const beltDesignText = $("#beltDesign option:selected").text();
      const sizeDesignId = $("#sizeDesign").val();
      const sizeDesignText = $("#sizeDesign option:selected").text();

      // Safe handling of serial number
      const serialNum1Element = $("#serial_num1");
      const serialNum1 = serialNum1Element.length && serialNum1Element.val() ? serialNum1Element.val().trim() : "";

      console.log("Adding DAG item:", {
        beltDesignId, beltDesignText, sizeDesignId, sizeDesignText, serialNum1
      });

      // Check if required fields are filled
      if (!beltDesignId) {
        swal("Error!", "Please select Belt Design.", "error");
        return;
      }

      if (!serialNum1) {
        swal("Error!", "Please enter Serial Number.", "error");
        return;
      }

      // Check validation for 'assigned', 'received', 'rejected_company' status
      const statusElement = $("#dag_status");
      const statusValue = statusElement.val() || ""; // removed duplicate declaration

      if (['assigned', 'received', 'rejected_company'].includes(statusValue)) {
        if (!$("#dag_company_id").val() || $("#dag_company_id").val() == "0") {
          swal("Error!", "Please select a Company.", "error");
          return;
        }

        if (!$("#receipt_no").val()) {
          swal("Error!", "Please enter Receipt Number.", "error");
          return;
        }
        if (!$("#job_number").val()) {
          swal("Error!", "Please enter Job Number.", "error");
          return;
        }
      }

      // Get company field values with safe checks
      const companyElement = $("#dag_company_id");
      const companyId = companyElement.length ? (companyElement.val() || "") : "";
      const companyText = companyElement.length ? (companyElement.find("option:selected").text() || "") : "";

      const issuedDateElement = $("#company_issued_date");
      const issuedDate = issuedDateElement.length ? (issuedDateElement.val() || "") : "";

      const deliveryDateElement = $("#company_delivery_date");
      const deliveryDate = deliveryDateElement.length ? (deliveryDateElement.val() || "") : "";

      const receiptNoElement = $("#receipt_no");
      const receiptNo = receiptNoElement.length ? (receiptNoElement.val() || "") : "";

      const brandElement = $("#brand_id");
      const brandId = brandElement.length ? (brandElement.val() || "") : "";
      const brandText = brandElement.length ? (brandElement.find("option:selected").text() || "") : "";

      const jobNumberElement = $("#job_number");
      const jobNumber = jobNumberElement.length ? (jobNumberElement.val() || "") : "";

      // reuse statusElement and statusValue declared above
      const statusText = statusElement.length ? (statusElement.find("option:selected").text() || "") : "";

      const ucElement = $("#uc");
      const ucValue = ucElement.length ? (ucElement.val() || "") : "";

      // Get customer info
      const customerId = $("#customer_id").val() || "";
      const customerCode = $("#customer_code").val() || "";
      const customerName = $("#customer_name").val() || "";

      // New item-level fields
      const myNumber = $("#my_number").val() || "";
      const receivedDate = $("#received_date").val() || "";
      const customerIssueDate = $("#customer_issue_date").val() || "";
      const customerRequestDate = $("#customer_request_date").val() || "";
      const vehicleNo = $("#vehicle_no").val() || "";

      const newRow = $(`
        <tr class="dag-item-row">
          <td>${myNumber || '<span class="text-muted">N/A</span>'}<input type="hidden" name="my_number[]" class="my_number" value="${myNumber}"></td>
          <td>${receivedDate || '<span class="text-muted">N/A</span>'}<input type="hidden" name="received_date[]" class="received_date" value="${receivedDate}"></td>
          <td>${customerIssueDate || '<span class="text-muted">N/A</span>'}<input type="hidden" name="customer_issue_date[]" class="customer_issue_date" value="${customerIssueDate}"></td>
          <td>${customerRequestDate || '<span class="text-muted">N/A</span>'}<input type="hidden" name="customer_request_date[]" class="customer_request_date" value="${customerRequestDate}"></td>
          <td>${sizeDesignText || '<span class="text-muted">N/A</span>'}<input type="hidden" name="size_design_id[]" class="size_id" value="${sizeDesignId}"></td>
          <td>${beltDesignText || '<span class="text-muted">N/A</span>'}<input type="hidden" name="belt_design_id[]" class="belt_id" value="${beltDesignId}"></td>
          <td>${serialNum1 || '<span class="text-muted">N/A</span>'}<input type="hidden" name="serial_num1[]" class="serial_num1" value="${serialNum1}"></td>
          <td>${customerCode || '<span class="text-muted">N/A</span>'}<input type="hidden" name="item_customer_id[]" class="item_customer_id" value="${customerId}"><input type="hidden" name="customer_name[]" class="customer_name" value="${customerName}"></td>
          <td>${vehicleNo || '<span class="text-muted">N/A</span>'}<input type="hidden" name="vehicle_no[]" class="vehicle_no" value="${vehicleNo}"></td>
          <td>${jobNumber || '<span class="text-muted">N/A</span>'}<input type="hidden" name="job_number[]" value="${jobNumber}"></td>
          <td>${statusText || '<span class="text-muted">N/A</span>'}<input type="hidden" name="status[]" value="${statusValue}"></td>
          <td>${ucValue || '<span class="text-muted">N/A</span>'}<input type="hidden" name="uc[]" class="uc" value="${ucValue}"></td>
          <td>${brandText || '<span class="text-muted">N/A</span>'}<input type="hidden" name="brand_id[]" class="brand_id" value="${brandId}"></td>
          <td>${deliveryDate || '<span class="text-muted">N/A</span>'}<input type="hidden" name="company_delivery_date[]" class="company_delivery_date" value="${deliveryDate}"></td>
          <td>
            <button type="button" class="btn btn-warning btn-sm edit-item">Edit</button>
            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
          </td>
        </tr>
      `);

      console.log("Appending row to #dagItemsBody");
      console.log("Table body exists:", $("#dagItemsBody").length > 0);
      console.log("Current rows before append:", $("#dagItemsBody tr").length);

      // Hide the "no items" row first
      $("#noDagItemRow").hide();

      // Then append the new row
      $("#dagItemsBody").append(newRow);

      console.log("Current rows after append:", $("#dagItemsBody tr").length);
      console.log("DAG item rows:", $(".dag-item-row").length);

      // Reset the form inputs
      resetDagInputs();

      // Focus back to belt design for next entry
      $("#beltDesign").focus();

      // Show success message
      console.log("Item added successfully");

    } catch (error) {
      console.error("Error in addDagItem:", error);
      swal("Error!", "An error occurred while adding the item. Please check the console for details.", "error");
    }
  }



  $("#addDagItemBtn").click(function (e) {
    e.preventDefault();
    addDagItem();
  });


  $("#beltDesign, #sizeDesign, #serial_num1").on("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      addDagItem();
    }
  });

  $(document).on("click", ".remove-item", function () {
    $(this).closest("tr").remove();

  });

  $("#create").click(function (event) {
    event.preventDefault();

    if (!$("#ref_no").val().trim()) {
      swal({
        title: "Error!",
        text: "Reference Number is required to proceed.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    let dagItems = [];
    $(".dag-item-row").each(function () {
      dagItems.push({
        belt_id: $(this).find("input[name='belt_design_id[]']").val(),
        size_id: $(this).find("input[name='size_design_id[]']").val(),
        serial_num1: $(this).find("input[name='serial_num1[]']").val(),
        company_delivery_date: $(this).find("input[name='company_delivery_date[]']").val(),
        brand_id: $(this).find("input[name='brand_id[]']").val(),
        job_number: $(this).find("input[name='job_number[]']").val(),
        status: $(this).find("input[name='status[]']").val(),
        uc: $(this).find("input[name='uc[]']").val(),
        customer_id: $(this).find("input[name='item_customer_id[]']").val() || null,
        // New item-level fields
        my_number: $(this).find("input[name='my_number[]']").val(),
        received_date: $(this).find("input[name='received_date[]']").val(),
        customer_issue_date: $(this).find("input[name='customer_issue_date[]']").val(),
        customer_request_date: $(this).find("input[name='customer_request_date[]']").val(),
        vehicle_no: $(this).find("input[name='vehicle_no[]']").val()
      });
    });

    if (dagItems.length === 0) {
      swal({
        title: "Error!",
        text: "Please add at least one DAG item before saving.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    $(".someBlock").preloader();
    const formData = new FormData($("#form-data")[0]);
    formData.append("create", true); // Create flag
    formData.append("dag_items", JSON.stringify(dagItems));

    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: formData,
      async: false,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (result) {
        console.log("DAG Create Response:", result);
        $(".someBlock").preloader("remove");
        if (result.status === "success") {
          // Reset the form and clear all inputs
          resetDagForm();

          // Clear DAG items table
          $("#dagItemsBody").empty();
          $("#dagItemsBody").append(`
            <tr id="noDagItemRow">
              <td colspan="12" class="text-center text-muted">No items added</td>
            </tr>
          `);

          // Clear invoice items table
          $("#dagItemsBodyInvoice").empty();
          $("#dagItemsBodyInvoice").append(`
            <tr id="noDagItemRow">
              <td colspan="6" class="text-center text-muted">No items found</td>
            </tr>
          `);

          // Reset totals
          $("#subTotal, #finalTotal").val("0.00");

          // Show success message and then fully reload the page
          swal("Success!", "DAG created successfully!", "success");
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          swal("Error!", result.message || "Something went wrong while creating.", "error");
        }
      },
      error: function (xhr, status, error) {
        $(".someBlock").preloader("remove");
        console.error("AJAX Error:", status, error);
        console.error("Response:", xhr.responseText);
        swal("Error!", "Failed to create DAG. Please check console for details.", "error");
      }
    });
  });



  $("#update").click(function (event) {
    event.preventDefault();
    if (!$("#ref_no").val().trim()) {
      swal({
        title: "Error!",
        text: "Reference Number is required to proceed.",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }


    if (!$("#remark").val().trim()) {
      swal({
        title: "Error!",
        text: "Dag Remark added.!",
        type: "error",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }


    $(".someBlock").preloader();
    const formData = new FormData($("#form-data")[0]);
    formData.append("update", true);
    formData.append("dag_id", $("#id").val());

    let dagItems = [];
    $(".dag-item-row").each(function () {
      dagItems.push({
        belt_id: $(this).find("input[name='belt_design_id[]']").val(),
        size_id: $(this).find("input[name='size_design_id[]']").val(),
        serial_num1: $(this).find("input[name='serial_num1[]']").val(),
        company_delivery_date: $(this).find("input[name='company_delivery_date[]']").val(),
        brand_id: $(this).find("input[name='brand_id[]']").val(),
        job_number: $(this).find("input[name='job_number[]']").val(),
        status: $(this).find("input[name='status[]']").val(),
        uc: $(this).find("input[name='uc[]']").val(),
        customer_id: $(this).find("input[name='item_customer_id[]']").val() || null,
        // New item-level fields
        my_number: $(this).find("input[name='my_number[]']").val(),
        received_date: $(this).find("input[name='received_date[]']").val(),
        customer_issue_date: $(this).find("input[name='customer_issue_date[]']").val(),
        customer_request_date: $(this).find("input[name='customer_request_date[]']").val(),
        vehicle_no: $(this).find("input[name='vehicle_no[]']").val()
      });

    });
    formData.append("dag_items", JSON.stringify(dagItems));

    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: formData,
      async: false,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "JSON",
      success: function (result) {
        $(".someBlock").preloader("remove");
        if (result.status === "success") {
          swal("Success!", "DAG updated successfully!", "success");
          setTimeout(() => location.reload(), 2000);
        } else {
          swal("Error!", "Something went wrong while updating.", "error");
        }
      },
    });
  });


  $(document).on("click", ".edit-item", function () {
    const row = $(this).closest("tr");

    // Populate item-level fields
    $("#beltDesign").val(row.find(".belt_id").val()).trigger("change");
    $("#sizeDesign").val(row.find(".size_id").val()).trigger("change");
    $("#serial_num1").val(row.find(".serial_num1").val());
    $("#brand_id").val(row.find(".brand_id").val()).trigger("change");
    $("#uc").val(row.find(".uc").val());
    $("#job_number").val(row.find("input[name='job_number[]']").val());
    $("#dag_status").val(row.find("input[name='status[]']").val()).trigger("change");

    // New item-level fields
    $("#my_number").val(row.find(".my_number").val());
    $("#received_date").val(row.find(".received_date").val());
    $("#customer_issue_date").val(row.find(".customer_issue_date").val());
    $("#customer_request_date").val(row.find(".customer_request_date").val());
    $("#vehicle_no").val(row.find(".vehicle_no").val());

    // Company Delivery Date
    $("#company_delivery_date").val(row.find(".company_delivery_date").val());

    // Handle Customer Info
    const customerId = row.find("input[name='item_customer_id[]']").val();
    if (customerId) {
      $("#customer_id").val(customerId);
      const customerCell = row.find("input[name='item_customer_id[]']").closest("td");
      const customerCode = customerCell.text().trim();
      $("#customer_code").val(customerCode);
      const customerName = row.find(".customer_name").val() || "";
      $("#customer_name").val(customerName);
    } else {
      $("#customer_id").val("");
      $("#customer_code").val("");
      $("#customer_name").val("");
    }

    row.remove();

    $("#beltDesign").focus();
  });


  $(document).on("click", "#searchDagBtn", function () {
    loadDagTable();
  });

  $(document).on("keypress", "#dagSearchInput", function (e) {
    if (e.which === 13) { // Enter key
      loadDagTable();
    }
  });

  $('#mainDagModel').on('shown.bs.modal', function () {
    loadDagTable();
  });

  function loadDagTable() {
    const searchTerm = $("#dagSearchInput").val().trim();

    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: { load_dags: true, search: searchTerm },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          $("#mainDagTableBody").html(response.html);
        } else {
          $("#mainDagTableBody").html('<tr><td colspan="7" class="text-center text-muted">No DAGs found</td></tr>');
        }
      },
      error: function () {
        $("#mainDagTableBody").html('<tr><td colspan="7" class="text-center text-danger">Error loading DAGs</td></tr>');
      }
    });
  }

  $(document).on("click", ".select-dag", function () {
    const data = $(this).data();

    $("#id").val(data.id);
    $("#dag_id").val(data.id);
    $("#ref_no").val(data.ref_no);
    $("#job_number").val(data.job_number);
    $("#department_id").val(data.department_id).trigger("change");
    $("#customer_id").val(data.customer_id).trigger("change");


    $("#customer_code").val(data.customer_code);
    $("#customer_name").val(data.customer_name);
    $("#vehicle_no").val(data.vehicle_no);
    $("#my_number").val(data.my_number);
    $("#customer_issue_date").val(data.customer_issue_date);

    $("#received_date").val(data.received_date);
    $("#delivery_date").val(data.delivery_date);
    $("#customer_request_date").val(data.customer_request_date);

    // Company header fields
    $("#dag_company_id").val(data.dag_company_id).trigger("change");
    $("#receipt_no").val(data.receipt_no);
    $("#company_issued_date").val(data.company_issued_date);
    $("#company_status").val(data.company_status).trigger("change");

    $("#remark").val(data.remark);

    $("#create").hide();
    $("#dagModel").modal("hide");
    $("#mainDagModel").modal("hide");

    $("#noDagItemRow").hide();
    $("#invoiceTable").hide();
    $("#dagTableHide").show();
    $("#addItemTable").hide();
    $("#quotationTableHide").hide();



    $("#dagItemsBody").empty();
    $("#print").data("dag-id", data.id);
    $("#print").show();
    $("#update").show();
    $.ajax({
      url: "ajax/php/create-dag.php",
      type: "POST",
      data: { dag_id: data.id },
      dataType: "json",
      success: function (res) {
        if (res.status === "success") {
          const items = res.data;
          console.log("Total items received:", items.length);
          console.log("Items data:", items);

          // Clear the table first
          $("#dagItemsBody").empty();
          console.log("Table cleared, current rows:", $("#dagItemsBody tr").length);

          items.forEach((item, index) => {
            console.log(`Processing item ${index + 1}:`, {
              serial: item.serial_number,
              company: item.dag_company_name,
              brand: item.brand_name,
              job: item.job_number
            });

            try {
              const row = `
  <tr class="dag-item-row">
    <td>${item.my_number || '<span class="text-muted">N/A</span>'}<input type="hidden" name="my_number[]" class="my_number" value="${item.my_number || ''}"></td>
    <td>${item.received_date || '<span class="text-muted">N/A</span>'}<input type="hidden" name="received_date[]" class="received_date" value="${item.received_date || ''}"></td>
    <td>${item.customer_issue_date || '<span class="text-muted">N/A</span>'}<input type="hidden" name="customer_issue_date[]" class="customer_issue_date" value="${item.customer_issue_date || ''}"></td>
    <td>${item.customer_request_date || '<span class="text-muted">N/A</span>'}<input type="hidden" name="customer_request_date[]" class="customer_request_date" value="${item.customer_request_date || ''}"></td>
    <td>${item.size_name || '<span class="text-muted">N/A</span>'}<input type="hidden" name="size_design_id[]" class="size_id" value="${item.size_id}"></td>
    <td>${item.belt_title || '<span class="text-muted">N/A</span>'}<input type="hidden" name="belt_design_id[]" class="belt_id" value="${item.belt_id}"></td>
    <td>${item.serial_number || '<span class="text-muted">N/A</span>'}<input type="hidden" name="serial_num1[]" class="serial_num1" value="${item.serial_number}"></td>
    <td>${item.customer_code || '<span class="text-muted">N/A</span>'}<input type="hidden" name="item_customer_id[]" class="item_customer_id" value="${item.customer_id || ''}"><input type="hidden" name="customer_name[]" class="customer_name" value="${item.customer_name || ''}"></td>
    <td>${item.vehicle_no || '<span class="text-muted">N/A</span>'}<input type="hidden" name="vehicle_no[]" class="vehicle_no" value="${item.vehicle_no || ''}"></td>
    <td>${item.job_number || '<span class="text-muted">N/A</span>'}<input type="hidden" name="job_number[]" value="${item.job_number}"></td>
    <td>${item.status || '<span class="text-muted">N/A</span>'}<input type="hidden" name="status[]" value="${item.status}"></td>
    <td>${item.uc || '<span class="text-muted">N/A</span>'}<input type="hidden" name="uc[]" class="uc" value="${item.uc}"></td>
    <td>${item.brand_name || '<span class="text-muted">N/A</span>'}<input type="hidden" name="brand_id[]" class="brand_id" value="${item.brand_id}"></td>
    <td>${item.company_delivery_date || '<span class="text-muted">N/A</span>'}<input type="hidden" name="company_delivery_date[]" class="company_delivery_date" value="${item.company_delivery_date || ''}"></td>
    <td>
      <button type="button" class="btn btn-warning btn-sm edit-item">Edit</button>
      <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
    </td>
  </tr>`;

              console.log(`About to append row ${index + 1}`);
              $("#dagItemsBody").append(row);
              console.log(`Rows in table after appending item ${index + 1}:`, $("#dagItemsBody tr").length);
              console.log(`DAG item rows:`, $(".dag-item-row").length);

              const price = parseFloat(item.casing_cost) || 0;
              const qty = parseFloat(item.qty) || 0;
              const total = price * qty;

              const invoiceRow = `
                <tr class="dag-item-row clickable-row">
                  <td>${$("#vehicle_no").val()}</td>
                  <td>${item.belt_title}</td>
                  <td>${item.barcode || ''}</td>
                  <td>${qty}</td>
                  <td><input type="number" class="form-control form-control-sm price" value="${price}"></td>
                  <td><input type="text" class="form-control form-control-sm totalPrice" value="${total.toFixed(2)}" readonly>
                  <input type="hidden" class="dag_item_id" value="${item.id}" />
                  </td>
                </tr>`;
              $("#dagItemsBodyInvoice").append(invoiceRow);

            } catch (error) {
              console.error(`Error processing item ${index + 1}:`, error);
            }
          });

          console.log("Final table state:");
          console.log("Total rows in dagItemsBody:", $("#dagItemsBody tr").length);
          console.log("Total dag-item-row elements:", $(".dag-item-row").length);

          calculateTotals();

        } else {
          swal("Warning!", "No items returned for this DAG.", "warning");
        }
      },
      error: function () {
        swal("Error!", "Failed to load DAG items.", "error");
      },
    });
  });

  $(document).on("click", "#print", function (e) {
    e.preventDefault();

    const dagId = $(this).data("dag-id");
    if (!dagId) {
      swal("Error!", "No DAG selected to print.", "error");
      return;
    }

    // Redirect to print page
    window.open(`dag-receipt-print.php?id=${dagId}`, "_blank");
  });


  function calculateTotals() {
    let subTotal = 0;

    $("#dagItemsBodyInvoice tr").each(function () {
      const price = parseFloat($(this).find('.price').val()) || 0;
      const qty = parseFloat($(this).find("td:eq(3)").text()) || 0;
      const rowTotal = price * qty;


      // Update totalPrice input (using class, not id)
      $(this).find('input.totalPrice').val(rowTotal.toFixed(2));

      subTotal += rowTotal;
    });

    const discountStr = $("#disTotal").val().replace(/,/g, '').trim();
    const discountPercent = parseFloat(discountStr) || 0;
    const discountAmount = (subTotal * discountPercent) / 100;

    const finalTotal = subTotal - discountAmount;

    $("#subTotal").val(subTotal.toFixed(2));
    $("#finalTotal").val(finalTotal.toFixed(2));

    if (finalTotal < subTotal) {
      $("#finalTotal").css("color", "red");
    } else {
      $("#finalTotal").css("color", "");
    }
  }

  // Handle price input changes dynamically
  $(document).on('input', '.price', function () {
    const row = $(this).closest('tr');
    const price = parseFloat($(this).val()) || 0;
    const qty = parseFloat(row.find("td:eq(3)").text()) || 0;

    const total = price * qty;
    row.find('.totalPrice').val(total.toFixed(2));

    // Enable discount input if needed
    $("#disTotal").prop("disabled", false);

    calculateTotals();
  });

  // Discount input triggers recalculation
  $(document).on("input", "#disTotal", function () {
    setTimeout(() => {
      calculateTotals();
    }, 10);
  });

  // Delete DAG functionality
  $(".delete-dag").click(function (event) {
    event.preventDefault();

    const dagId = $("#id").val();
    if (!dagId || dagId === "0") {
      swal("Error!", "Please select a DAG to delete.", "error");
      return;
    }

    // Show confirmation dialog
    swal({
      title: "Are you sure?",
      text: "Once deleted, you will not be able to recover this DAG!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel!",
      closeOnConfirm: false,
      closeOnCancel: false
    }, function (isConfirm) {
      if (isConfirm) {
        // User confirmed, proceed with deletion
        $(".someBlock").preloader();

        $.ajax({
          url: "ajax/php/create-dag.php",
          type: "POST",
          data: { delete: true, dag_id: dagId },
          dataType: "JSON",
          success: function (result) {
            $(".someBlock").preloader("remove");
            if (result.status === "success") {
              swal("Deleted!", "The DAG has been deleted.", "success");
              // Reset form and redirect or reload
              resetDagForm();
              setTimeout(() => {
                location.reload();
              }, 2000);
            } else {
              swal("Error!", result.message || "Failed to delete DAG.", "error");
            }
          },
          error: function () {
            $(".someBlock").preloader("remove");
            swal("Error!", "An error occurred while deleting the DAG.", "error");
          }
        });
      } else {
        // User cancelled
        swal("Cancelled", "The DAG is safe :)", "error");
      }
    });
  });



});
