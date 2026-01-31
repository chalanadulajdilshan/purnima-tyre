jQuery(document).ready(function ($) {
    // Initialize datepickers with range validation
    $(".date-picker").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        onSelect: function (selectedDate) {
            var input = $(this);
            var dateMin = null;

            if (input.attr('id') === 'from_date') {
                // If this is the 'from' date, update the 'to' date's minDate
                dateMin = $(this).datepicker('getDate');
                $("#to_date").datepicker("option", "minDate", dateMin);

                // If 'to' date is before 'from' date, reset it
                var toDate = $("#to_date").datepicker('getDate');
                if (toDate && toDate < dateMin) {
                    $("#to_date").datepicker('setDate', dateMin);
                }
            }
        }
    });

    // Set initial min date for 'to' date picker
    $("#from_date").on('change', function () {
        var fromDate = $(this).datepicker('getDate');
        if (fromDate) {
            $("#to_date").datepicker("option", "minDate", fromDate);
        }
    });

    // Initialize DataTable - check if already initialized
    if ($.fn.DataTable.isDataTable('#dag-report-table')) {
        $('#dag-report-table').DataTable().destroy();
    }

    var dagReportTable = $('#dag-report-table').DataTable({
        responsive: true,
        searching: true,
        ordering: true,
        paging: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
        language: {
            emptyTable: "No DAG records found for the selected filters",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            lengthMenu: "Show _MENU_ entries",
            search: "Search:",
            zeroRecords: "No matching records found"
        },
        columnDefs: [
            { orderable: false, targets: [0, 13] }, // Disable sorting on # and Actions columns
            { className: 'text-center', targets: [10] }, // Center customer issue date column
            { className: 'text-end', targets: [11] } // Right-align amount column
        ],
        order: [[3, 'desc']] // Default sort by Date descending
    });

    // Filter button click handler
    $("#btn-filter").click(function (event) {
        event.preventDefault();

        // Validation
        if (!$('#from_date').val() || $('#from_date').val().length === 0) {
            swal({
                title: "Error!",
                text: "Please select a from date!",
                type: "error",
                timer: 2000,
                showConfirmButton: false,
            });
            return false;
        } else if (!$('#to_date').val() || $('#to_date').val().length === 0) {
            swal({
                title: "Error!",
                text: "Please select a to date!",
                type: 'error',
                timer: 2000,
                showConfirmButton: false
            });
            return false;
        }

        // Show preloader
        $('.someBlock').preloader({
            text: 'Loading...',
            zIndex: '99999'
        });

        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var status = $('#filter_status').val();
        var dagNo = $('#dag_no').val();
        var myNumber = $('#my_number').val();
        var beltId = $('#belt_id').val();
        var sizeId = $('#size_id').val();

        $.ajax({
            url: "ajax/php/dag-viw-report.php",
            type: 'POST',
            dataType: 'json',
            data: {
                from_date: fromDate,
                to_date: toDate,
                status: status,
                dag_no: dagNo,
                my_number: myNumber,
                belt_id: beltId,
                size_id: sizeId,
                action: 'filter'
            },
            success: function (result) {
                // Hide preloader on success
                $('.someBlock').preloader('remove');

                if (result.status === 'success') {
                    updateDagReportTable(result.reports || []);
                } else {
                    swal({
                        title: "Error!",
                        text: result.message || "Something went wrong.",
                        type: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function (xhr, status, error) {
                // Hide preloader on error
                $('.someBlock').preloader('remove');
                console.error("Error:", error);
                swal({
                    title: "Error!",
                    text: "Failed to load data. Please try again.",
                    type: 'error'
                });
            }
        });

        return false;
    });

    // Reset button click handler
    $('#btn-reset-filter').on('click', function (e) {
        e.preventDefault();

        // Reset the form
        $('#filter-form').trigger('reset');

        // Clear datepickers
        $('.date-picker').datepicker('setDate', null);

        // Clear the DataTable
        var dagReportTable = $('#dag-report-table').DataTable();
        dagReportTable.clear().draw();
    });
});

// Update table with data using DataTables
function updateDagReportTable(reports) {
    var dagReportTable = $('#dag-report-table').DataTable();

    // Clear existing data
    dagReportTable.clear().draw();

    if (reports.length > 0) {
        // Add data rows
        $.each(reports, function (index, report) {
            var status = report.status || 'Pending';
            var statusClass = getStatusClass(status);

            dagReportTable.row.add([
                index + 1,
                report.ref_no || '',
                report.my_number || '',
                formatDate(report.received_date),
                report.customer_name || '',
                report.company_name || '',
                report.department_name || '',
                report.belt_design || '',
                report.serial_number || '',
                report.vehicle_no || '',
                '<div class="text-center">' + ((report.customer_issue_date && report.customer_issue_date !== '0000-00-00') ? formatDate(report.customer_issue_date) : '<span class="text-danger">Not Issued</span>') + '</div>',
                '<div class="text-end">' + parseFloat(report.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</div>',
                '<span class="badge ' + statusClass + ' font-size-12">' + capitalizeFirst(status) + '</span>',
                '<a href="dag-receipt-print.php?id=' + report.id + '" target="_blank" class="btn btn-info btn-sm"><i class="mdi mdi-printer"></i></a>'
            ]).draw(false);
        });
    }
}

// Get status badge class
function getStatusClass(status) {
    switch (status.toLowerCase()) {
        case 'received':
            return 'bg-success';
        case 'assigned':
            return 'bg-primary';
        case 'approved':
            return 'bg-info';
        case 'rejected_company':
        case 'rejected':
            return 'bg-danger';
        case 'pending':
        default:
            return 'bg-warning';
    }
}

// Format date to dd/mm/yyyy
function formatDate(dateString) {
    if (!dateString) return '';
    var date = new Date(dateString);
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    return day + '/' + month + '/' + year;
}

// Capitalize first letter
function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Print report function
function printReport() {
    window.print();
}

// Export to Excel function
function exportToExcel() {
    var fromDate = $('#from_date').val() || '';
    var toDate = $('#to_date').val() || '';
    var status = $('#filter_status').val() || '';
    var dagNo = $('#dag_no').val() || '';
    var myNumber = $('#my_number').val() || '';
    var beltId = $('#belt_id').val() || '';
    var sizeId = $('#size_id').val() || '';

    window.location.href = 'ajax/export-dag-report.php?from_date=' + encodeURIComponent(fromDate) +
        '&to_date=' + encodeURIComponent(toDate) +
        '&status=' + encodeURIComponent(status) +
        '&dag_no=' + encodeURIComponent(dagNo) +
        '&my_number=' + encodeURIComponent(myNumber) +
        '&belt_id=' + encodeURIComponent(beltId) +
        '&size_id=' + encodeURIComponent(sizeId);
}
