<div class="modal fade" id="kanbanModal" tabindex="-1" aria-labelledby="kanbanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kanbanModalLabel">Add Kanban</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="kanbanForm">
                    <input type="hidden" id="id_kanban" />
                    <div id="kanbanMultiInputs">
                        <div class="kanban-item border rounded p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select class="form-select project_select">
                                    <option value="">Select project</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id_project }}">{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kanban Name</label>
                                <input type="text" class="form-control kanban_name" maxlength="20" required />
                                <div class="invalid-feedback">Kanban name is required and max 20 chars.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Part Name</label>
                                <input type="text" class="form-control part_name" maxlength="20" required />
                                <div class="invalid-feedback">Part name is required and max 20 chars.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" class="form-control part_number" maxlength="20" required />
                                <div class="invalid-feedback">Part number is required and max 20 chars.</div>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-outline-danger btn-kanban-remove">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-primary" id="kanbanAddRow">Add More</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveKanban">Save</button>
            </div>
        </div>
    </div>
</div>
