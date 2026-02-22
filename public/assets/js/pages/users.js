
;(function(){
  function csrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function ajax(opts) {
    return $.ajax(Object.assign({
      headers: { 'X-CSRF-TOKEN': csrf() },
      error: function(xhr){
        const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Request failed';
        if (window.Swal) {
          Swal.fire({ icon: 'error', title: 'Error', text: msg });
        } else {
          alert(msg);
        }
      }
    }, opts));
  }

  var table;

  function initTable(){
    if (!document.getElementById('table-user')) return;
    
    if ($.fn.DataTable.isDataTable('#table-user')) {
        $('#table-user').DataTable().ajax.reload();
        return;
    }

    table = $('#table-user').DataTable({
        ajax: {
            url: '/users/list',
            dataSrc: ''
        },
        columns: [
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input user-checkbox" value="${data}">`;
                }
            },
            { 
                data: null, 
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'username' },
            { data: 'name' },
            { data: 'email' },
            { 
                data: 'status',
                render: function(data) {
                    if (data === 'admin') {
                        return '<span class="badge badge-outline-success me-1">Admin</span>';
                    } else if (data === 'user') {
                        return '<span class="badge badge-outline-dark me-1">User</span>';
                    } else {
                        return '<span class="badge badge-outline-secondary me-1">' + data + '</span>';
                    }
                }
            },
            { 
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    const uname = (row.username || '').replace(/"/g, '&quot;');
                    const name = (row.name || '').replace(/"/g, '&quot;');
                    const email = (row.email || '').replace(/"/g, '&quot;');
                    const status = (row.status || '').replace(/"/g, '&quot;');
                    return `<button class="btn btn-sm btn-outline-primary me-2 btn-u-edit" data-id="${data}" data-username="${uname}" data-name="${name}" data-email="${email}" data-status="${status}">Edit</button>` +
                           `<button class="btn btn-sm btn-outline-danger btn-u-delete" data-id="${data}">Delete</button>`;
                }
            }
        ],
        responsive: true
    });
  }

  function openUserModal(title, data){
    $('#userModalLabel').text(title || 'Add User');
    $('#id_user').val(data && data.id || '');
    $('#username').val(data && data.username || '').removeClass('is-invalid');
    $('#fullname').val(data && data.name || '').removeClass('is-invalid');
    $('#email').val(data && data.email || '').removeClass('is-invalid');
    $('#password').val('');
    $('#status_user').prop('checked', false);
    $('#status_admin').prop('checked', false);
    var status = (data && data.status) || 'user';
    if (status === 'admin') $('#status_admin').prop('checked', true); else $('#status_user').prop('checked', true);
    var modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
  }

  function closeUserModal(){
    var el = document.getElementById('userModal');
    var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
    if (modal) {
      modal.hide();
      setTimeout(function(){
        if (bootstrap.Modal.getInstance(el)) bootstrap.Modal.getInstance(el).dispose();
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
        el.classList.remove('show');
        el.setAttribute('aria-hidden','true');
        el.style.display = 'none';
        $('#userForm')[0].reset();
      }, 150);
    }
  }

  $(document).ready(function(){
    initTable();

    // Select All functionality
    $(document).on('change', '#selectAll', function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkDeleteBtn();
    });

    // Individual checkbox change
    $(document).on('change', '.user-checkbox', function() {
        var totalCheckboxes = $('.user-checkbox').length;
        var checkedCheckboxes = $('.user-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        toggleBulkDeleteBtn();
    });

    // Toggle bulk delete button visibility
    function toggleBulkDeleteBtn() {
        var checkedCount = $('.user-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#btnBulkDelete').removeClass('d-none');
        } else {
            $('#btnBulkDelete').addClass('d-none');
        }
    }

    // Bulk Delete functionality
    $('#btnBulkDelete').off('click').on('click', function() {
        var ids = [];
        $('.user-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete selected users?',
                text: `You are about to delete ${ids.length} users.`,
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Delete',
            }).then(function(result){
                if (result.isConfirmed) {
                    ajax({ url: '/users/bulk', method: 'DELETE', data: { ids: ids } }).done(function(){
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Users Deleted', timer: 1200, showConfirmButton: false });
                        }
                        if (table) table.ajax.reload();
                        $('#selectAll').prop('checked', false);
                        toggleBulkDeleteBtn();
                    });
                }
            });
        }
    });

    $('#btnUserAdd').off('click').on('click', function(){
      openUserModal('Add User', null);
    });
    $(document).on('change', '#status_admin', function(){
      if ($(this).is(':checked')) $('#status_user').prop('checked', false);
    });
    $(document).on('change', '#status_user', function(){
      if ($(this).is(':checked')) $('#status_admin').prop('checked', false);
    });

    $('#saveUser').off('click').on('click', function(){
      var id = $('#id_user').val();
      var username = $('#username').val().trim();
      var name = $('#fullname').val().trim();
      var email = $('#email').val().trim();
      var password = $('#password').val();
      var status = $('#status_admin').is(':checked') ? 'admin' : 'user';
      var invalid = false;
      [['#username', username], ['#fullname', name], ['#email', email]].forEach(function(pair){
        if(!pair[1]){ $(pair[0]).addClass('is-invalid'); invalid = true; } else { $(pair[0]).removeClass('is-invalid'); }
      });
      if (invalid){
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Please fill all required fields' });
        return;
      }
      var payload = { username: username, fullname: name, email: email, status: status };
      if (!id) {
        if(!password || password.length < 6){
          $('#password').addClass('is-invalid');
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Password is required (min 6 chars)' });
          return;
        }
        payload.password = password;
      } else if (password) {
        payload.password = password;
      }
      var req = id
        ? ajax({ url: '/users/' + id, method: 'PUT', data: payload })
        : ajax({ url: '/users', method: 'POST', data: payload });
      var $btn = $('#saveUser');
      $btn.prop('disabled', true);
      req.done(function(){
        closeUserModal();
        if (window.Swal) Swal.fire({ icon: 'success', title: id ? 'User updated' : 'User added', timer: 1500, showConfirmButton: false });
        if (table) table.ajax.reload();
      }).always(function(){ $btn.prop('disabled', false); });
    });

    $(document).on('click', '.btn-u-edit', function(){
      var id = $(this).data('id');
      var username = $(this).data('username');
      var name = $(this).data('name');
      var email = $(this).data('email');
      var status = $(this).data('status');
      openUserModal('Edit User', { id: id, username: username, name: name, email: email, status: status });
    });

    $(document).on('click', '.btn-u-delete', function(){
      var id = $(this).data('id');
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Delete this user?',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel'
        }).then(function(result){
          if (result.isConfirmed) {
            ajax({ url: '/users/' + id, method: 'DELETE' }).done(function(){
              if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
              if (table) table.ajax.reload();
            });
          }
        });
      } else {
        if(confirm('Delete this user?')){
          ajax({ url: '/users/' + id, method: 'DELETE' }).done(function(){ if (table) table.ajax.reload(); });
        }
      }
    });

    window.loadUsers = initTable;
  });
})();
