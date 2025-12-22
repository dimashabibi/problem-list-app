<div class="modal fade" id="problemDetailModal" tabindex="-1" aria-labelledby="problemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="problemDetailModalLabel">Problem Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Project:</strong> <span id="d_project"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Kanban:</strong> <span id="d_kanban"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Item:</strong> <span id="d_item"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Location:</strong> <span id="d_location"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Type:</strong> <span id="d_type"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong> 
                        <span id="d_status"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Reporter:</strong> <span id="d_reporter"></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Created At:</strong> <span id="d_created_at"></span>
                    </div>
                    <div class="col-12 mb-3">
                        <strong>Problem:</strong>
                        <textarea id="d_problem" class="form-control" cols="62" rows="6" disabled></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <strong>Cause:</strong>
                        <textarea id="d_cause" class="form-control" cols="62" rows="6" disabled></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <strong>Curative:</strong>
                        <textarea id="d_curative" class="form-control" cols="62" rows="6" disabled></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <strong>Attachment:</strong>
                        <div id="d_attachment"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
