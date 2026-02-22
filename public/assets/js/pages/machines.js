(function () {
    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute("content") : "";
    }

    function ajax(opts) {
        return $.ajax(
            Object.assign(
                {
                    headers: { "X-CSRF-TOKEN": csrf() },
                    error: function (xhr) {
                        const msg =
                            (xhr.responseJSON && xhr.responseJSON.message) ||
                            "Request failed";
                        if (window.Swal) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: msg,
                            });
                        } else {
                            alert(msg);
                        }
                    },
                },
                opts,
            ),
        );
    }

    var table;

    function initTable() {
        if (!document.getElementById("table-machine")) return;

        if ($.fn.DataTable.isDataTable("#table-machine")) {
            $("#table-machine").DataTable().ajax.reload();
            return;
        }

        table = $("#table-machine").DataTable({
            ajax: {
                url: "/machines/list",
                dataSrc: function (json) {
                    console.log(json); // Debug
                    return json;
                },
            },
            columns: [
                {
                    data: "id_machine",
                    orderable: false,
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="form-check-input machine-checkbox" value="${data}">`;
                    }
                },
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                { data: "name_machine", className: "text-uppercase" },
                { data: "description", className: "text-wrap" },
                {
                    data: "id_machine",
                    orderable: false,
                    render: function (data, type, row) {
                        const name = (row.name_machine || "").replace(
                            /"/g,
                            "&quot;",
                        );
                        const desc = (row.description || "").replace(
                            /"/g,
                            "&quot;",
                        );
                        return (
                            `<button class="btn btn-sm btn-outline-primary me-2 btn-edit" data-id="${data}" data-name="${name}" data-desc="${desc}">Edit</button>` +
                            `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${data}">Delete</button>`
                        );
                    },
                },
            ],
            responsive: true,
        });
    }

    function openModal(title, data) {
        $("#machineModalLabel").text(title || "Add Machine");
        $("#id_machine").val((data && data.id_machine) || "");
        $("#name_machine").val((data && data.name_machine) || "");
        $("#description").val((data && data.description) || "");
        var modal = new bootstrap.Modal(
            document.getElementById("machineModal"),
        );
        modal.show();
    }

    function closeModal() {
        var el = document.getElementById("machineModal");
        var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        if (modal) {
            modal.hide();
        }
    }

    $(document).ready(function () {
        initTable();

        // Select All functionality
        $(document).on('change', '#selectAll', function() {
            $('.machine-checkbox').prop('checked', $(this).prop('checked'));
            toggleBulkDeleteBtn();
        });

        // Individual checkbox change
        $(document).on('change', '.machine-checkbox', function() {
            var totalCheckboxes = $('.machine-checkbox').length;
            var checkedCheckboxes = $('.machine-checkbox:checked').length;
            $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            toggleBulkDeleteBtn();
        });

        // Toggle bulk delete button visibility
        function toggleBulkDeleteBtn() {
            var checkedCount = $('.machine-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#btnBulkDelete').removeClass('d-none');
            } else {
                $('#btnBulkDelete').addClass('d-none');
            }
        }

        // Bulk Delete functionality
        $('#btnBulkDelete').off('click').on('click', function() {
            var ids = [];
            $('.machine-checkbox:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) return;

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Delete selected machines?',
                    text: `You are about to delete ${ids.length} machines.`,
                    showCancelButton: true,
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Delete',
                }).then(function(result){
                    if (result.isConfirmed) {
                        ajax({ url: '/machines/bulk', method: 'DELETE', data: { ids: ids } }).done(function(){
                            if (window.Swal) {
                                Swal.fire({ icon: 'success', title: 'Machines Deleted', timer: 1200, showConfirmButton: false });
                            }
                            if (table) table.ajax.reload();
                            $('#selectAll').prop('checked', false);
                            toggleBulkDeleteBtn();
                        });
                    }
                });
            }
        });

        $("#btnAdd").on("click", function () {
            openModal("Add Machine", null);
        });

        $(document).on("click", ".btn-edit", function () {
            var id = $(this).data("id");
            var name = $(this).data("name");
            var desc = $(this).data("desc");
            openModal("Edit Machine", {
                id_machine: id,
                name_machine: name,
                description: desc,
            });
        });

        $(document).on("click", ".btn-delete", function () {
            var id = $(this).data("id");
            if (window.Swal) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteMachine(id);
                    }
                });
            } else {
                if (confirm("Are you sure?")) {
                    deleteMachine(id);
                }
            }
        });

        function deleteMachine(id) {
            ajax({
                url: "/machines/" + id,
                method: "DELETE",
            }).done(function () {
                if (window.Swal) {
                    Swal.fire(
                        "Deleted!",
                        "Machine has been deleted.",
                        "success",
                    );
                }
                table.ajax.reload();
            });
        }

        $("#btnSave").on("click", function () {
            var id = $("#id_machine").val();
            var name = $("#name_machine").val();
            var desc = $("#description").val();

            if (!name) {
                if (window.Swal)
                    Swal.fire({
                        icon: "error",
                        title: "Machine Name is required",
                    });
                else alert("Machine Name is required");
                return;
            }

            var url = id ? "/machines/" + id : "/machines/store";
            var method = id ? "PUT" : "POST";

            var data = {
                name_machine: name,
                description: desc,
            };

            var $btn = $(this);
            $btn.prop("disabled", true);

            ajax({
                url: url,
                method: method,
                data: data,
            })
                .done(function () {
                    closeModal();
                    table.ajax.reload();
                    if (window.Swal) {
                        Swal.fire(
                            "Saved!",
                            "Machine has been saved.",
                            "success",
                        );
                    }
                })
                .always(function () {
                    $btn.prop("disabled", false);
                });
        });

        // Expose initTable globally if needed
        window.loadMachines = initTable;
    });
})();
