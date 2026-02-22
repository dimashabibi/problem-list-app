
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
    if (!document.getElementById('table-location')) return;
    
    if ($.fn.DataTable.isDataTable('#table-location')) {
        $('#table-location').DataTable().ajax.reload();
        return;
    }

    table = $('#table-location').DataTable({
        ajax: {
            url: '/locations/list',
            dataSrc: ''
        },
        columns: [
            { 
                data: 'id_location',
                orderable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="form-check-input location-checkbox" value="${data}">`;
                }
            },
            { 
                data: null, 
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'location_name' },
            { data: 'description' },
            { 
                data: 'id_location',
                orderable: false,
                render: function(data, type, row) {
                    const name = (row.location_name || '').replace(/"/g, '&quot;');
                    const desc = (row.description || '').replace(/"/g, '&quot;');
                    return `<button class="btn btn-sm btn-outline-primary me-2 btn-l-edit" data-id="${data}" data-name="${name}" data-desc="${desc}">Edit</button>` +
                           `<button class="btn btn-sm btn-outline-danger btn-l-delete" data-id="${data}">Delete</button>`;
                }
            }
        ],
        responsive: true
    });
  }

  function openLocationModal(title, data){
    $('#locationModalLabel').text(title || 'Add Location');
    $('#id_location').val(data && data.id_location || '');
    $('#locationMultiInputs').find('.location-item:gt(0)').remove();
    var first = $('#locationMultiInputs .location-item').first();
    first.find('.location_name').val(data && data.location_name || '').removeClass('is-invalid');
    first.find('.location_description').val(data && data.description || '');
    var modal = new bootstrap.Modal(document.getElementById('locationModal'));
    modal.show();
  }

  function closeLocationModal(){
    var el = document.getElementById('locationModal');
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
        $('#locationForm')[0].reset();
        $('#locationMultiInputs').find('.location-item:gt(0)').remove();
      }, 150);
    }
  }

  function collectLocationItems(){
    var items = [];
    $('#locationMultiInputs .location-item').each(function(){
      var name = $(this).find('.location_name').val().trim();
      var desc = $(this).find('.location_description').val().trim();
      items.push({ location_name: name, description: desc });
    });
    return items;
  }

  $(document).ready(function(){
    initTable();

    // Select All functionality
    $(document).on('change', '#selectAll', function() {
        $('.location-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkDeleteBtn();
    });

    // Individual checkbox change
    $(document).on('change', '.location-checkbox', function() {
        var totalCheckboxes = $('.location-checkbox').length;
        var checkedCheckboxes = $('.location-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
        toggleBulkDeleteBtn();
    });

    // Toggle bulk delete button visibility
    function toggleBulkDeleteBtn() {
        var checkedCount = $('.location-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#btnBulkDelete').removeClass('d-none');
        } else {
            $('#btnBulkDelete').addClass('d-none');
        }
    }

    // Bulk Delete functionality
    $('#btnBulkDelete').off('click').on('click', function() {
        var ids = [];
        $('.location-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) return;

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Delete selected locations?',
                text: `You are about to delete ${ids.length} locations.`,
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Delete',
            }).then(function(result){
                if (result.isConfirmed) {
                    ajax({ url: '/locations/bulk', method: 'DELETE', data: { ids: ids } }).done(function(){
                        if (window.Swal) {
                            Swal.fire({ icon: 'success', title: 'Locations Deleted', timer: 1200, showConfirmButton: false });
                        }
                        if (table) table.ajax.reload();
                        $('#selectAll').prop('checked', false);
                        toggleBulkDeleteBtn();
                    });
                }
            });
        }
    });

    $('#btnLocationAdd').off('click').on('click', function(){
      openLocationModal('Add Location', null);
    });

    $('#locationAddRow').off('click').on('click', function(){
      var last = $('#locationMultiInputs .location-item').last();
      var clone = last.clone();
      clone.find('.location_name').val('').removeClass('is-invalid');
      clone.find('.location_description').val('');
      $('#locationMultiInputs').append(clone);
    });

    $(document).on('click', '.btn-location-remove', function(){
      var items = $('#locationMultiInputs .location-item');
      if (items.length > 1) $(this).closest('.location-item').remove();
    });

    $('#saveLocation').off('click').on('click', function(){
      var id = $('#id_location').val();
      var req;
      if (id) {
        var name = $('#locationMultiInputs .location-item').first().find('.location_name').val().trim();
        var desc = $('#locationMultiInputs .location-item').first().find('.location_description').val().trim();
        if(!name){
          $('#locationMultiInputs .location-item').first().find('.location_name').addClass('is-invalid');
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Location name is required' });
          return;
        }
        req = ajax({ url: '/locations/' + id, method: 'PUT', data: { location_name: name, description: desc } });
      } else {
        var items = collectLocationItems();
        var invalid = false;
        $('#locationMultiInputs .location-item').each(function(){
          var input = $(this).find('.location_name');
          if(!input.val().trim()){
            input.addClass('is-invalid');
            invalid = true;
          } else {
            input.removeClass('is-invalid');
          }
        });
        if (invalid) {
          if (window.Swal) Swal.fire({ icon: 'error', title: 'Add at least one location' });
          return;
        }
        req = ajax({ url: '/locations/bulk', method: 'POST', data: { items: items } });
      }
      var $btn = $('#saveLocation');
      $btn.prop('disabled', true);
      req.done(function(){
        closeLocationModal();
        if (window.Swal) Swal.fire({ icon: 'success', title: id ? 'Location updated' : 'Locations added', timer: 1500, showConfirmButton: false });
        if (table) table.ajax.reload();
      }).always(function(){ $btn.prop('disabled', false); });
    });

    $(document).on('click', '.btn-l-edit', function(){
      var id = $(this).data('id');
      var name = $(this).data('name');
      var desc = $(this).data('desc');
      openLocationModal('Edit Location', { id_location: id, location_name: name, description: desc });
    });

    $(document).on('click', '.btn-l-delete', function(){
      var id = $(this).data('id');
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Delete this location?',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel'
        }).then(function(result){
          if (result.isConfirmed) {
            ajax({ url: '/locations/' + id, method: 'DELETE' }).done(function(){
              if (window.Swal) Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
              if (table) table.ajax.reload();
            });
          }
        });
      } else {
        if(confirm('Delete this location?')){
          ajax({ url: '/locations/' + id, method: 'DELETE' }).done(function(){ if (table) table.ajax.reload(); });
        }
      }
    });

    window.loadLocations = initTable;
  });
})();
