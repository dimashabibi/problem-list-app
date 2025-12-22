<div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalLabel">Add Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="projectForm">
                    <input type="hidden" id="id_project" />
                    <div id="multiInputs">
                        <div class="project-item border rounded p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Project Name</label>
                                <input type="text" class="form-control project_name" maxlength="20" required />
                                <div class="invalid-feedback">Project name is required and max 20 chars.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control project_description" rows="2"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-outline-danger btn-remove-row">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-primary" id="addRow">Add More</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProject">Save</button>
            </div>
        </div>
    </div>
</div>
