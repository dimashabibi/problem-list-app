<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="id_user" />
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" maxlength="50" required />
                        <div class="invalid-feedback">Username is required and unique.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullname" maxlength="100" required />
                        <div class="invalid-feedback">Full name is required.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" maxlength="255" required />
                        <div class="invalid-feedback">Valid email is required.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" minlength="6" />
                        <div class="form-text">Leave blank when editing to keep current password.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="status_user" value="user">
                            <label class="form-check-label" for="status_user">User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="status_admin" value="admin">
                            <label class="form-check-label" for="status_admin">Admin</label>
                        </div>
                        <div class="form-text">Choose one. If both selected, Admin will be used.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUser">Save</button>
            </div>
        </div>
    </div>
</div>
