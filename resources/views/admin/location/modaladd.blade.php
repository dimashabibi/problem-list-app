<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalLabel">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="locationForm">
                    <input type="hidden" id="id_location" />
                    <div id="locationMultiInputs">
                        <div class="location-item border rounded p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Location Name</label>
                                <input type="text" class="form-control location_name" maxlength="50" required />
                                <div class="invalid-feedback">Location name is required and max 50 chars.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control location_description" rows="2"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-outline-danger btn-location-remove">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-primary" id="locationAddRow">Add More</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLocation">Save</button>
            </div>
        </div>
    </div>
    </div>
