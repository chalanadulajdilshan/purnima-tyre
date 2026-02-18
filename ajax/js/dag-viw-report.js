jQuery(document).ready(function ($) {
    // Clear filter inputs on load if no URL parameters are present to prevent browser autofill
    if (window.location.search === '') {

        $('#filter_status').val('');
        $('#dag_no').val('');
        $('#my_number').val('');
        $('#belt_id').val('');
        $('#size_id').val('');
    }

    // Initialize datepickers with range validation


    // ── AJAX DataTable – DAG parent rows ─────────────────────────
    var dagReportTable = $('#dag-report-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "ajax/php/dag-viw-report.php",
            type: "POST",
            data: function (d) {
                d.action = 'load_dags';

                d.status = $('#filter_status').val();
                d.dag_no = $('#dag_no').val();
                d.my_number = $('#my_number').val();
                d.belt_id = $('#belt_id').val();
                d.size_id = $('#size_id').val();
            },
            dataSrc: function (json) {
                return json.data || [];
            },
            error: function (xhr) {
                console.error("Server Error:", xhr.responseText);
            }
        },
        columns: [
            {
                data: null,
                className: "details-control",
                orderable: false,
                defaultContent: '<i class="mdi mdi-plus-circle-outline" style="font-size:18px; cursor:pointer; color:#556ee6;"></i>',
                width: "30px"
            },
            { data: "ref_no", defaultContent: "" },
            {
                data: "company_issued_date",
                render: function (data) { return formatDate(data); }
            },
            { data: "company_name", defaultContent: "" },
            {
                data: "item_count",
                className: "text-center",
                render: function (data) {
                    return '<span class="badge bg-info font-size-12">' + (data || 0) + '</span>';
                }
            },
            {
                data: "total_amount",
                className: "text-end",
                render: function (data) {
                    return parseFloat(data || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return '<a href="dag-receipt-print.php?id=' + data.id + '" target="_blank" class="btn btn-info btn-sm" title="Print Receipt">' +
                        '<i class="mdi mdi-printer"></i></a>';
                }
            }
        ],
        order: [[2, 'desc']],
        lengthMenu: [10, 25, 50, 100],
        pageLength: 25,
        responsive: false,
        language: {
            emptyTable: "No DAG records found",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            lengthMenu: "Show _MENU_ entries",
            search: "Search:",
            zeroRecords: "No matching records found",
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        }
    });

    // ── Expand / Collapse – fetch DAG items ──────────────────────
    $('#dag-report-table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = dagReportTable.row(tr);
        var icon = $(this).find('i.mdi');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('mdi-minus-circle-outline').addClass('mdi-plus-circle-outline');
        } else {
            icon.removeClass('mdi-plus-circle-outline').addClass('mdi-loading mdi-spin');

            var dagId = row.data().id;

            $.ajax({
                url: "ajax/php/dag-viw-report.php",
                type: "POST",
                dataType: "json",
                data: { action: 'get_dag_items', dag_id: dagId },
                success: function (result) {
                    icon.removeClass('mdi-loading mdi-spin');

                    if (result.status === 'success' && result.data && result.data.length > 0) {
                        row.child(renderItemsTable(result.data, dagId)).show();
                        tr.addClass('shown');
                        icon.addClass('mdi-minus-circle-outline');
                    } else {
                        row.child('<div class="p-3 text-muted text-center">No items found for this DAG</div>').show();
                        tr.addClass('shown');
                        icon.addClass('mdi-minus-circle-outline');
                    }
                },
                error: function () {
                    icon.removeClass('mdi-loading mdi-spin').addClass('mdi-plus-circle-outline');
                    console.error("Failed to load DAG items");
                }
            });
        }
    });

    // ── Render DAG items sub-table ───────────────────────────────
    function renderItemsTable(items, dagId) {
        var html = '<div class="p-3" style="background-color: #f8f9fa;">';
        html += '<h6 class="mb-2"><i class="mdi mdi-format-list-bulleted me-1"></i> DAG Items (' + items.length + ')</h6>';
        html += '<table class="table table-sm table-bordered dag-items-table mb-0">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>#</th>';
        html += '<th>My Number</th>';
        html += '<th>Belt Design</th>';
        html += '<th>Size</th>';
        html += '<th>Serial No</th>';
        html += '<th>Vehicle No</th>';
        html += '<th>Customer</th>';
        html += '<th>Total Amount</th>';
        html += '<th>Issued Date</th>';
        html += '<th>Status</th>';
        html += '<th>Actions</th>';
        html += '</tr>';
        html += '</thead><tbody>';

        $.each(items, function (index, item) {
            var status = item.status || 'Pending';
            var statusClass = getStatusClass(status);

            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + (item.my_number || '-') + '</td>';
            html += '<td>' + (item.belt_title || '-') + '</td>';
            html += '<td>' + (item.size_name || '-') + '</td>';
            html += '<td>' + (item.serial_number || '-') + '</td>';
            html += '<td>' + (item.vehicle_no || '-') + '</td>';
            html += '<td>' + (item.customer_name || '-') + '</td>';
            html += '<td class="text-end">' + parseFloat(item.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>';

            var issueDateDisplay = '<span class="text-muted">Not Issued Yet</span>';
            if (item.customer_issue_date && item.customer_issue_date !== '0000-00-00') {
                issueDateDisplay = formatDate(item.customer_issue_date);
            }
            html += '<td>' + issueDateDisplay + '</td>';
            html += '<td>';
            html += '<span class="badge ' + statusClass + ' font-size-12">' + capitalizeFirst(status) + '</span>';
            if (item.reassigned_to_ref_no) {
                html += '<br><span class="badge bg-primary font-size-12 mt-1"><i class="mdi mdi-arrow-right me-1"></i> ' + item.reassigned_to_ref_no + '</span>';
            }
            html += '</td>';
            html += '<td>';
            html += '<a href="dag-receipt-print.php?id=' + dagId + '&item_id=' + item.id + '" target="_blank" class="btn btn-info btn-sm" title="Print Receipt"><i class="mdi mdi-printer"></i></a>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    // ── Filter button ────────────────────────────────────────────
    $("#btn-filter").click(function (event) {
        event.preventDefault();
        dagReportTable.ajax.reload();
        return false;
    });

    // ── Reset button ─────────────────────────────────────────────
    $('#btn-reset-filter').on('click', function (e) {
        e.preventDefault();
        $('#from_date').val('');
        $('#to_date').val('');
        $('#filter_status').val('');
        $('#dag_no').val('');
        $('#my_number').val('');
        $('#belt_id').val('');
        $('#size_id').val('');
        dagReportTable.ajax.reload();
    });
});

// ── Helper functions ─────────────────────────────────────────────

function getStatusClass(status) {
    switch ((status || '').toLowerCase()) {
        case 'received': return 'bg-success';
        case 'assigned': return 'bg-primary';
        case 'approved': return 'bg-info';
        case 'rejected_company':
        case 'rejected_store':
        case 'rejected': return 'bg-danger';
        case 'pending':
        default: return 'bg-warning';
    }
}

function formatDate(dateString) {
    if (!dateString) return '';
    if (dateString === '0000-00-00') return '';

    // Handle YYYY-MM-DD manually to avoid timezone issues
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
        var parts = dateString.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    var date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = date.getFullYear();
    return day + '/' + month + '/' + year;
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function printReport() {
    window.print();
}
