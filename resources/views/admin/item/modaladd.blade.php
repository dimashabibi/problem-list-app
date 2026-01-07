<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm">
                    <input type="hidden" id="id_item" />
                    <div id="multiInputs">
                        <div class="item-item border rounded p-3 mb-3">
                            <div class="mb-3">
                                <label class="form-label">Item Name</label>
                                <input type="text" class="form-control item_name" maxlength="50" required />
                                <div class="invalid-feedback">Item name is required and max 50 chars.</div>
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
                <button type="button" class="btn btn-primary" id="saveItem">Save</button>
            </div>
        </div>
    </div>
</div>
