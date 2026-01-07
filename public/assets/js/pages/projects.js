
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
    if (!document.getElementById('table-project')) return;
    
    // If table already initialized, just reload
    if ($.fn.DataTable.isDataTable('#table-project')) {
        $('#table-project').DataTable().ajax.reload();
        return;
    }

    table = $('#table-project').DataTable({
        ajax: {
            url: '/projects/list',
            dataSrc: function (json) {
        console.log(json);  // Debug untuk melihat data yang diterima
        return json;
        }
        },
        columns: [
            {
                data: 'id_project',
                orderable: false,
                className: 'text-center',
                render: function (data) {
                    return `<input type="checkbox" class="form-check-input project-checkbox" value="${data}">`;
                }
            },
            { 
                data: null, 
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'project_name', className: 'text-uppercase' },
            { data: 'description', className: 'text-wrap' },
            { 
                data: 'id_project',
                orderable: false,
                render: function(data, type, row) {
                    // Escape data for attributes
                    const name = (row.project_name || '').replace(/"/g, '&quot;');
                    const desc = (row.description || '').replace(/"/g, '&quot;');
                    return `<button class="btn btn-sm btn-outline-primary me-2 btn-edit" data-id="${data}" data-name="${name}" data-desc="${desc}">Edit</button>` +
                           `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${data}">Delete</button>`;
                }
            }
        ],
        responsive: true,
        drawCallback: function() {
            $('#selectAll').prop('checked', false);
            toggleBulkDeleteBtn();
        }
    });
  }

  function toggleBulkDeleteBtn() {
      var count = $('.project-checkbox:checked').length;
      if (count > 0) {
          $('#btnBulkDelete').removeClass('d-none').text(`Delete Selected (${count})`);
      } else {
          $('#btnBulkDelete').addClass('d-none');
      }
  }

  function openModal(title, data){
    $('#projectModalLabel').text(title || 'Add Project');
    $('#id_project').val(data && data.id_project || '');
    $('#project_name').val(data && data.project_name || '');
    $('#description').val(data && data.description || '');
    var modal = new bootstrap.Modal(document.getElementById('projectModal'));
    modal.show();
  }

  function closeModal(){
    var el = document.getElementById('projectModal');
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
        $('#projectForm')[0].reset();
      }, 150);
    }
  }

  function collectItems(){
    var items = [];
    $('#multiInputs .project-item').each(function(){
      var name = $(this).find('.project_name').val().trim();
      var desc = $(this).find('.project_description').val().trim();
      if (name) items.push({ project_name: name, description: desc });
    });
    return items;
  }

  $(document).ready(function(){
    initTable();

    // Select All checkbox
    $(document).on('change', '#selectAll', function() {
        $('.project-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkDeleteBtn();
    });

    // Individual checkbox
    $(document).on('change', '.project-checkbox', function() {
        var allChecked = $('.project-checkbox:checked').length === $('.project-checkbox').length;
        $('#selectAll').prop('checked', allChecked);
        toggleBulkDeleteBtn();
    });

    // Bulk Delete Button
    $('#btnBulkDelete').off('click').on('click', function() {
        var ids = [];
        $('.project-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete selected projects?',
                text: `You are about to delete ${ids.length} projects.`,
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Delete',
            }).then(function(result){
                if (result.isConfirmed) {
                    ajax({ url: '/projects/bulk', method: 'DELETE', data: { ids: ids } }).done(function(){
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Projects Deleted', timer: 1200, showConfirmButton: false });
                        }
                        if (table) table.ajax.reload();
                    });
                }
            });
        } else {
            if(confirm(`Delete ${ids.length} selected projects?`)){
                ajax({ url: '/projects/bulk', method: 'DELETE', data: { ids: ids } }).done(function(){ if (table) table.ajax.reload(); });
            }
        }
    });

    $('#btnAdd').off('click').on('click', function(){
      openModal('Add Project', null);
      $('#id_project').val('');
      $('#multiInputs').find('.project-item:gt(0)').remove();
      $('#multiInputs .project-item').find('.project_name').val('');
      $('#multiInputs .project-item').find('.project_description').val('');
    });

    $('#saveProject').off('click').on('click', function(){
      var id = $('#id_project').val();
      if (id) {
        var name = $('#multiInputs .project-item').first().find('.project_name').val().trim();
        var desc = $('#multiInputs .project-item').first().find('.project_description').val().trim();
        
        if(!name){
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Project name is required' });
          return;
        }
        var req = ajax({ url: '/projects/' + id, method: 'PUT', data: { project_name: name, description: desc } });
      } else {
        var items = collectItems();
        if(items.length === 0){
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Add at least one project' });
          return;
        }
        var invalid = false;
        $('#multiInputs .project-item').each(function(){
          var input = $(this).find('.project_name');
          if(!input.val().trim()){
            input.addClass('is-invalid');
            invalid = true;
          } else {
            input.removeClass('is-invalid');
          }
        });
        if (invalid) return;
        var req = ajax({ url: '/projects/bulk', method: 'POST', data: { items: items } });
      }
      var $btn = $('#saveProject');
      $btn.prop('disabled', true);
      req.done(function(){
           closeModal();
           if (window.Swal) {
             Swal.fire({
               icon: 'success',
               title: id ? 'Project updated' : 'Projects added',
               timer: 1500,
               showConfirmButton: false
             });
           }
           if (table) table.ajax.reload();
         })
         .always(function(){ $btn.prop('disabled', false); });
    });

    $('#addRow').off('click').on('click', function(){
      var last = $('#multiInputs .project-item').last();
      var clone = last.clone();
      clone.find('.project_name').val('').removeClass('is-invalid');
      clone.find('.project_description').val('');
      $('#multiInputs').append(clone);
    });

    $(document).on('click', '.btn-remove-row', function(){
      var items = $('#multiInputs .project-item');
      if (items.length > 1) {
        $(this).closest('.project-item').remove();
      }
    });

    $(document).on('click', '.btn-edit', function(){
      var id = $(this).data('id');
      var name = $(this).data('name');
      var desc = $(this).data('desc');
      openModal('Edit Project', { id_project: id, project_name: name, description: desc });
      $('#multiInputs').find('.project-item:gt(0)').remove();
      $('#multiInputs .project-item').find('.project_name').val(name);
      $('#multiInputs .project-item').find('.project_description').val(desc);
    });

    $(document).on('click', '.btn-delete', function(){
      var id = $(this).data('id');
      if (!id) return;
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Delete this project?',
          showCancelButton: true,
          cancelButtonText: 'Cancel',
          confirmButtonText: 'Delete',
        }).then(function(result){
          if (result.isConfirmed) {
            ajax({ url: '/projects/' + id, method: 'DELETE' }).done(function(){
              if (window.Swal) {
                Swal.fire({ icon: 'success', title: 'Project Deleted', timer: 1200, showConfirmButton: false });
              }
              if (table) table.ajax.reload();
            });
          }
        });
      } else {
        if(confirm('Delete this project?')){
          ajax({ url: '/projects/' + id, method: 'DELETE' }).done(function(){ if (table) table.ajax.reload(); });
        }
      }
    });

    window.loadProjects = initTable;
  });
})();
