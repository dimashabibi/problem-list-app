<div class="modal fade" id="problemDetailModal" tabindex="-1" aria-labelledby="problemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="problemDetailModalLabel">Problem Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <!-- Attachments Section (Carousel at the top) -->
                        <div class="mb-4">
                            <h6 class="card-title text-muted mb-3">Attachments</h6>
                            <div id="attachment-carousel"></div>
                        </div>

                        <!-- Details Section -->
                        <div class="container py-3">
                            <form>
                                <div class="row g-3">
                                    <!-- Row 1 -->
                                    <div class="col-md-6">
                                        <label for="d_project" class="form-label">Project</label>
                                        <input type="text" id="d_project" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_kanban" class="form-label">Kanban</label>
                                        <input type="text" id="d_kanban" class="form-control" disabled>
                                    </div>

                                    <!-- Row 2 -->
                                    <div class="col-md-6">
                                        <label for="d_item" class="form-label">Item</label>
                                        <input type="text" id="d_item" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_location" class="form-label">Location</label>
                                        <input type="text" id="d_location" class="form-control" disabled>
                                    </div>

                                    <!-- Row 3 -->
                                    <div class="col-md-6">
                                        <label for="d_type" class="form-label">Type</label>
                                        <input type="text" id="d_type" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_status" class="form-label">Status</label>
                                        <input type="text" id="d_status" class="form-control" disabled>
                                    </div>

                                    <!-- Row 4 -->
                                    <div class="col-md-6">
                                        <label for="d_reporter" class="form-label">Reporter</label>
                                        <input type="text" id="d_reporter" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_created_at" class="form-label">Created At</label>
                                        <input type="text" id="d_created_at" class="form-control" disabled>
                                    </div>

                                    <!-- Row 5 (Textarea) -->
                                    <div class="col-12">
                                        <label for="d_problem" class="form-label">Problem</label>
                                        <textarea id="d_problem" class="form-control" rows="3" disabled></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="d_cause" class="form-label">Cause</label>
                                        <textarea id="d_cause" class="form-control" rows="3" disabled></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="d_curative" class="form-label">Curative</label>
                                        <textarea id="d_curative" class="form-control" rows="3" disabled></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
