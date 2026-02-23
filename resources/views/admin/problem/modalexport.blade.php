<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-control" id="flt_type">
                            <option value="manufacturing">Manufacturing</option>
                            <option value="kentokai">Kentokai</option>
                            <option value="ks">KS</option>
                            <option value="kd">KD</option>
                            <option value="sk">SK</option>
                            <option value="buyoff">Buy Off</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project</label>
                        <select class="form-control" id="flt_project">
                            <option value="">All Projects</option>
                            @foreach (\App\Models\Project::orderBy('project_name')->get() as $p)
                                <option value="{{ $p->id_project }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kanban</label>
                        <select class="form-control" id="flt_kanban">
                            <option value="">All Kanbans</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group Code</label>
                        <select class="form-control" id="flt_group_code">
                            <option value="">Select Group Code</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="flt_start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" id="flt_end_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger me-auto" id="btnResetFilter">Reset Filter</button>
                <button type="button" class="btn btn-primary" id="btnApplyFilter">Apply</button>
                <button type="button" class="btn btn-success" id="btnApplyExport">Apply & Export</button>
            </div>
        </div>
    </div>
</div>
