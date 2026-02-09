<div class="modal fade" id="machineModal" tabindex="-1" aria-labelledby="machineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="machineModalLabel">Add Machine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="machineForm">
                    <input type="hidden" id="id_machine" name="id_machine">
                    <div class="mb-3">
                        <label for="name_machine" class="form-label">Machine Name</label>
                        <input type="text" class="form-control" id="name_machine" name="name_machine" required maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSave">Save</button>
            </div>
        </div>
    </div>
</div>
