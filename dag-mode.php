<div class="modal fade" id="dagModel" tabindex="-1" role="dialog" aria-labelledby="dagModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dagModalLabel">Select DAG</h5>

                <div class="input-group ms-3" style="max-width: 500px;">
                    <input type="text" id="dagSearchInput" class="form-control"
                        placeholder="Search by Job No, Serial No, Ref No, Company...">
                    <button class="btn btn-outline-primary" type="button" id="searchDagBtn">
                        Search Dag
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <table id="dagTable" class="table table-bordered table-hover dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th style="width:30px;"></th>
                            <th>#</th>
                            <th>Ref No</th>
                            <th>Company Name</th>
                            <th>Company Receipt No</th>
                            <th>Company Issued Date</th>
                            <th>Company Status</th>
                            <th>Items</th>
                        </tr>
                    </thead>

                    <tbody id="dagTableBody">
                        <!-- DAGs will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>