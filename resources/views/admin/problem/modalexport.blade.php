<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Problem Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-control" id="exp_type" disabled>
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
                        <select class="form-control" id="exp_project">
                            <option value="">All Projects</option>
                            @foreach (\App\Models\Project::orderBy('project_name')->get() as $p)
                                <option value="{{ $p->id_project }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kanban</label>
                        <select class="form-control" id="exp_kanban">
                            <option value="">All Kanbans</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group Code</label>
                        <select class="form-control" id="exp_group_code">
                            <option value="">Select Group Code</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btnDoExport">Download Excel</button>
            </div>
        </div>
    </div>
</div>
