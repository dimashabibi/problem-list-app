
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
    if (!document.getElementById('table-kanban')) return;

    var pid = $('#kanbanFilterProject').val();
    var url = '/kanbans/list' + (pid ? ('?project_id=' + encodeURIComponent(pid)) : '');

    if ($.fn.DataTable.isDataTable('#table-kanban')) {
        // Just reload with new URL
        var dt = $('#table-kanban').DataTable();
        dt.ajax.url(url).load();
        return;
    }

    table = $('#table-kanban').DataTable({
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
            { 
                data: 'project',
                render: function(data) {
                    return data ? data.project_name : '-';
                }
            },
            { data: 'kanban_name' },
            { 
                data: 'id_kanban',
                orderable: false,
                render: function(data, type, row) {
                    const pname = row.project ? row.project.project_name.replace(/"/g, '&quot;') : '';
                    const kname = (row.kanban_name || '').replace(/"/g, '&quot;');
                    return `<button class="btn btn-sm btn-outline-primary me-2 btn-k-edit" data-id="${data}" data-pname="${pname}" data-kname="${kname}">Edit</button>` +
                           `<button class="btn btn-sm btn-outline-danger btn-k-delete" data-id="${data}">Delete</button>`;
                }
            }
        ],
        responsive: true
    });
  }

  function openKanbanModal(title, data){
    $('#kanbanModalLabel').text(title || 'Add Kanban');
    $('#id_kanban').val(data && data.id_kanban || '');
    if (data && data.project_id) $('#project_id').val(data.project_id); else $('#project_id').val('');
    $('#kanbanMultiInputs').find('.kanban-item:gt(0)').remove();
    var first = $('#kanbanMultiInputs .kanban-item').first();
    first.find('.kanban_name').val(data && data.kanban_name || '').removeClass('is-invalid');
    var modal = new bootstrap.Modal(document.getElementById('kanbanModal'));
    modal.show();
  }

  function closeKanbanModal(){
    var el = document.getElementById('kanbanModal');
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
        $('#kanbanForm')[0].reset();
        $('#kanbanMultiInputs').find('.kanban-item:gt(0)').remove();
      }, 150);
    }
  }

  function collectKanbanItems(){
    var items = [];
    $('#kanbanMultiInputs .kanban-item').each(function(){
      var pid = $(this).find('.project_select').val();
      var name = $(this).find('.kanban_name').val().trim();
      items.push({ project_id: pid, kanban_name: name });
    });
    return items;
  }

  $(document).ready(function(){
    initTable();

    $('#btnKanbanAdd').off('click').on('click', function(){
      openKanbanModal('Add Kanban', null);
    });
    
    $(document).on('change', '#kanbanFilterProject', function(){
      initTable();
    });

    $('#kanbanAddRow').off('click').on('click', function(){
      var last = $('#kanbanMultiInputs .kanban-item').last();
      var clone = last.clone();
      clone.find('.kanban_name').val('').removeClass('is-invalid');
      clone.find('.project_select').val('');
      $('#kanbanMultiInputs').append(clone);
    });

    $(document).on('click', '.btn-kanban-remove', function(){
      var items = $('#kanbanMultiInputs .kanban-item');
      if (items.length > 1) $(this).closest('.kanban-item').remove();
    });

    $('#saveKanban').off('click').on('click', function(){
      var id = $('#id_kanban').val();
      if (id) {
        var pid = $('#kanbanMultiInputs .kanban-item').first().find('.project_select').val();
        var name = $('#kanbanMultiInputs .kanban-item').first().find('.kanban_name').val().trim();
        if(!pid || !name){
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Project and kanban name are required' });
          return;
        }
        var req = ajax({ url: '/kanbans/' + id, method: 'PUT', data: { project_id: pid, kanban_name: name } });
        req.done(function(){
          closeKanbanModal();
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Kanban updated', timer: 1500, showConfirmButton: false });
          if (table) table.ajax.reload();
        });
      } else {
        var items = collectKanbanItems();
        var invalid = false;
        items.forEach(function(it){
          if(!it.project_id || !it.kanban_name){
            invalid = true;
          }
        });
        if (invalid){
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Each row needs a project and kanban name' });
          return;
        }
        var req = ajax({ url: '/kanbans/bulk', method: 'POST', data: { items: items } });
        req.done(function(){
          closeKanbanModal();
          if (window.Swal) Swal.fire({ icon: 'success', title: 'Kanbans added', timer: 1500, showConfirmButton: false });
          if (table) table.ajax.reload();
        });
      }
    });

    $(document).on('click', '.btn-k-edit', function(){
      var id = $(this).data('id');
      var pname = $(this).data('pname');
      var kname = $(this).data('kname');
      // Find pid from select using pname
      var pid = $('#kanbanMultiInputs .project_select option').filter(function(){ return $(this).text() === pname; }).val();
      openKanbanModal('Edit Kanban', { id_kanban: id, project_id: pid, kanban_name: kname });
      $('#kanbanMultiInputs .kanban-item').first().find('.project_select').val(pid);
    });

    $(document).on('click', '.btn-k-delete', function(){
      var id = $(this).data('id');
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Delete this kanban?',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel'
        }).then(function(result){
          if (result.isConfirmed) {
            ajax({ url: '/kanbans/' + id, method: 'DELETE' }).done(function(){
              if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
              if (table) table.ajax.reload();
            });
          }
        });
      } else {
        if(confirm('Delete this kanban?')){
          ajax({ url: '/kanbans/' + id, method: 'DELETE' }).done(function(){ if (table) table.ajax.reload(); });
        }
      }
    });

    window.loadKanbans = initTable;
  });
})();
