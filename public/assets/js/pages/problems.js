
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
    if (!document.getElementById('table-problem')) return;
    
    var activeType = $('.nav-tabs .nav-link.active').data('type') || 'manufacturing';
    var url = '/problems/list?type=' + encodeURIComponent(activeType);

    if ($.fn.DataTable.isDataTable('#table-problem')) {
        var dt = $('#table-problem').DataTable();
        dt.ajax.url(url).load();
        return;
    }

    table = $('#table-problem').DataTable({
        ajax: {
            url: url,
            dataSrc: ''
        },
        columns: [
            { 
                data: null, 
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'created_at', render: function(data) { return data ? new Date(data).toLocaleDateString() : '-'; } },
            { data: 'project', className: 'text-uppercase' },
            { data: 'kanban', className: 'text-uppercase' },
            { data: 'item' },
            { data: 'location' },
            { data: 'problem'  },
            {
                data: 'status',
                render: function(data, type, row) {
                    var safeData = data || 'dispatched';
                    var badgeClass = 'secondary';
                    var label = safeData;
                    if (safeData === 'dispatched') { badgeClass = 'warning'; label = 'Dispatched'; }
                    else if (safeData === 'in_progress') { badgeClass = 'primary'; label = 'In Progress'; }
                    else if (safeData === 'closed') { badgeClass = 'success'; label = 'Closed'; }
                    
                    return `<div class="dropdown">
                              <button class="btn btn-sm btn-${badgeClass} dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ${label}
                              </button>
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item btn-status" href="{{ url('/problems/{id}/status')}}" data-id="${row.id_problem}" data-status="dispatched">Dispatched</a></li>
                                <li><a class="dropdown-item btn-status" href="{{ url('/problems/{id}/status')}}" data-id="${row.id_problem}" data-status="in_progress">In Progress</a></li>
                                <li><a class="dropdown-item btn-status" href="{{ url('/problems/{id}/status')}}" data-id="${row.id_problem}" data-status="closed">Closed</a></li>
                              </ul>
                            </div>`;
                }
            },
            { 
                data: 'id_problem',
                orderable: false,
                render: function(data, type, row) {
                    return `<button class="btn btn-sm btn-outline-info me-2 btn-detail" data-id="${data}">Detail</button>` +
                           `<button class="btn btn-sm btn-outline-success me-2 btn-excel" data-id="${data}">Excel</button>` +
                           `<button class="btn btn-sm btn-outline-danger btn-p-delete" data-id="${data}">Delete</button>`;
                }
            }
        ],
        responsive: true
    });
  }

  function openProblemModal(){
    $('#problemForm')[0].reset();
    $('#p_kanban').empty().append('<option value=\"\">Select kanban</option>');
    // Explicitly reset radio to manufacturing
    $('#type_manufacturing').prop('checked', true);

    // Reset UI to Select Mode
    $('#p_project').removeClass('d-none');
    $('#new_project_name').addClass('d-none');
    $('#toggleProjectMode').html('<i class="bi bi-plus-lg"></i> New');

    $('#p_kanban').removeClass('d-none');
    $('#new_kanban_name').addClass('d-none');
    $('#toggleKanbanMode').html('<i class="bi bi-plus-lg"></i> New').prop('disabled', false);

    var modal = new bootstrap.Modal(document.getElementById('problemModal'));
    modal.show();
  }

  $(document).ready(function(){
    initTable();
    
    // Handle tab click
    $('.nav-tabs .nav-link').on('shown.bs.tab', function(e){
      initTable();
    });

    $('#btnProblemAdd').off('click').on('click', function(){
      openProblemModal();
    });

    $('#p_project').off('change').on('change', function(){
      var pid = $(this).val();
      $('#p_kanban').empty().append('<option value=\"\">Select kanban</option>');
      if (!pid) return;
      ajax({ url: '/kanbans/list', method: 'GET', data: { project_id: pid } })
        .done(function(items){
          items.forEach(function(k){
            $('#p_kanban').append('<option value="'+k.id_kanban+'">'+(k.kanban_name)+'</option>');
          });
        });
    });

    // Toggle Project Mode
    $(document).on('click', '#toggleProjectMode', function(){
        var isSelect = !$('#p_project').hasClass('d-none');
        if (isSelect) {
            $('#p_project').addClass('d-none');
            $('#new_project_name').removeClass('d-none').focus();
            $(this).html('<i class="bi bi-x-lg"></i> Cancel');
            
            // If new project, force new kanban
            if (!$('#p_kanban').hasClass('d-none')) {
                $('#toggleKanbanMode').trigger('click');
            }
            $('#toggleKanbanMode').prop('disabled', true); // Cannot switch back to select kanban if new project
        } else {
            $('#p_project').removeClass('d-none');
            $('#new_project_name').addClass('d-none').val('');
            $(this).html('<i class="bi bi-plus-lg"></i> New');
            
            $('#toggleKanbanMode').prop('disabled', false);
        }
    });

    // Toggle Kanban Mode
    $(document).on('click', '#toggleKanbanMode', function(){
        var isSelect = !$('#p_kanban').hasClass('d-none');
        if (isSelect) {
            $('#p_kanban').addClass('d-none');
            $('#new_kanban_name').removeClass('d-none').focus();
            $(this).html('<i class="bi bi-x-lg"></i> Cancel');
        } else {
            $('#p_kanban').removeClass('d-none');
            $('#new_kanban_name').addClass('d-none').val('');
            $(this).html('<i class="bi bi-plus-lg"></i> New');
        }
    });

    $('#saveProblem').off('click').on('click', function(){
      var pid = $('#p_project').val();
      var kid = $('#p_kanban').val();
      
      var newProjectName = $('#new_project_name').hasClass('d-none') ? '' : $('#new_project_name').val().trim();
      var newKanbanName = $('#new_kanban_name').hasClass('d-none') ? '' : $('#new_kanban_name').val().trim();

      // Validation logic update
      if (newProjectName) pid = 'new'; // Placeholder to pass basic check
      if (newKanbanName) kid = 'new';

      var item = $('#p_item').val().trim();
      var lid = $('#p_location').val();
      var type = $('input[name="p_type"]:checked').val();
      if (!type) {
         type = 'manufacturing'; // Fallback if undefined
      }
      var prob = $('#p_problem').val().trim();
      var cause = $('#p_cause').val().trim();
      var curative = $('#p_curative').val().trim();
      var file = $('#p_attachment')[0].files[0];
      
      if((!pid && !newProjectName) || (!kid && !newKanbanName) || !item || !lid || !type || !prob || !cause || !curative){
        if (window.Swal) Swal.fire({ icon: 'error', title: 'All fields except attachment are required' });
        return;
      }
      var fd = new FormData();
      if (newProjectName) fd.append('new_project_name', newProjectName);
      else fd.append('id_project', pid);

      if (newKanbanName) fd.append('new_kanban_name', newKanbanName);
      else fd.append('id_kanban', kid);

      fd.append('item', item);
      fd.append('id_location', lid);
      fd.append('type', type);
      fd.append('problem', prob);
      fd.append('cause', cause);
      fd.append('curative', curative);
      if (file) fd.append('attachment', file);

      var $btn = $('#saveProblem');
      $btn.prop('disabled', true);
      $.ajax({
        url: '/problems',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf() },
        data: fd,
        processData: false,
        contentType: false
      }).done(function(){
        var el = document.getElementById('problemModal');
        var modal = bootstrap.Modal.getInstance(el);
        if (modal) modal.hide();
        if (window.Swal) Swal.fire({ icon: 'success', title: 'Problem added', timer: 1500, showConfirmButton: false });
        
        // Reset inputs
        $('#p_project').removeClass('d-none');
        $('#new_project_name').addClass('d-none').val('');
        $('#toggleProjectMode').html('<i class="bi bi-plus-lg"></i> New');
        
        $('#p_kanban').removeClass('d-none');
        $('#new_kanban_name').addClass('d-none').val('');
        $('#toggleKanbanMode').html('<i class="bi bi-plus-lg"></i> New').prop('disabled', false);
        
        // Reload project dropdown just in case
        // Ideally we should reload the whole page or fetch projects again, but let's just reload table
        if (table) table.ajax.reload();
      }).always(function(){ $btn.prop('disabled', false); });
    });

    $(document).on('click', '.btn-p-delete', function(){
      var id = $(this).data('id');
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Delete this problem?',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel'
        }).then(function(result){
          if (result.isConfirmed) {
            ajax({ url: '/problems/' + id, method: 'DELETE' }).done(function(){
              if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
              if (table) table.ajax.reload();
            });
          }
        });
      } else {
        if(confirm('Delete this problem?')){
          ajax({ url: '/problems/' + id, method: 'DELETE' }).done(function(){ if (table) table.ajax.reload(); });
        }
      }
    });

    $(document).on('click', '.btn-detail', function(){
        var id = $(this).data('id');
        var tr = $(this).closest('tr');
        var row = table.row(tr).data();

        if (!row) {
             // Fallback for edge cases
             var allData = table.rows().data().toArray();
             row = allData.find(function(item){ return item.id_problem == id; });
        }
        
        if (!row) return;

        $('#d_project').text(row.project || '-');
        $('#d_kanban').text(row.kanban || '-');
        $('#d_item').text(row.item || '-');
        $('#d_location').text(row.location || '-');
        $('#d_type').text(row.type || '-');
        $('#d_status').text(row.status || '-');
        $('#d_reporter').text(row.reporter || '-');
        $('#d_created_at').text(row.created_at ? new Date(row.created_at).toLocaleDateString() : '-');
        $('#d_problem').text(row.problem || '-');
        $('#d_cause').text(row.cause || '-');
        $('#d_curative').text(row.curative || '-');
        
        if (row.attachment) {
            $('#d_attachment').html(`<a href="/storage/${row.attachment}" target="_blank" class="btn btn-sm btn-outline-primary">View Attachment</a>`);
        } else {
            $('#d_attachment').text('No attachment');
        }

        var modal = new bootstrap.Modal(document.getElementById('problemDetailModal'));
        modal.show();
    });

    $(document).on('click', '.btn-excel', function(){
      var id = $(this).data('id');
      if (!id) return;

      var btn = $(this);
      var originalText = btn.html();
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

      fetch('/problems/' + id + '/export', {
          method: 'GET',
          headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrf()
          }
      })
      .then(response => {
          if (response.ok) {
              return response.blob().then(blob => {
                  var url = window.URL.createObjectURL(blob);
                  var a = document.createElement('a');
                  a.href = url;
                  // Try to get filename from header
                  var filename = 'problem_' + id + '.xlsx';
                  var disposition = response.headers.get('Content-Disposition');
                  if (disposition && disposition.indexOf('attachment') !== -1) {
                      var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                      var matches = filenameRegex.exec(disposition);
                      if (matches != null && matches[1]) { 
                        filename = matches[1].replace(/['"]/g, '');
                        // decodeURI if needed
                        filename = decodeURIComponent(filename);
                      }
                  }
                  a.download = filename;
                  document.body.appendChild(a);
                  a.click();
                  a.remove();
                  window.URL.revokeObjectURL(url);
              });
          } else {
              return response.json().then(data => {
                  throw new Error(data.message || 'Error exporting Excel');
              }).catch(err => {
                  // If json parse fails, use status text
                  throw new Error('Export failed: ' + response.statusText);
              });
          }
      })
      .catch(error => {
          if (window.Swal) {
              Swal.fire({ icon: 'error', title: 'Export Failed', text: error.message });
          } else {
              alert(error.message);
          }
      })
      .finally(() => {
          btn.prop('disabled', false).html(originalText);
      });
    });

    window.loadProblems = initTable;
  });
})();
