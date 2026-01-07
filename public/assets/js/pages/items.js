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
                opts
            )
        );
    }

    var table;

    function initTable() {
        if (!document.getElementById("table-item")) return;

        // If table already initialized, just reload
        if ($.fn.DataTable.isDataTable("#table-item")) {
            $("#table-item").DataTable().ajax.reload();
            return;
        }

        table = $("#table-item").DataTable({
            ajax: {
                url: "/items/list",
                dataSrc: function (json) {
                    console.log(json); // Debug
                    return json;
                },
            },
            columns: [
                {
                    data: "id_item",
                    orderable: false,
                    className: "text-center",
                    render: function (data) {
                        return `<input type="checkbox" class="form-check-input item-checkbox" value="${data}">`;
                    },
                },
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                { data: "item_name", className: "text-uppercase" },
                {
                    data: "id_item",
                    orderable: false,
                    render: function (data, type, row) {
                        // Escape data for attributes
                        const name = (row.item_name || "").replace(
                            /"/g,
                            "&quot;"
                        );
                        const desc = (row.description || "").replace(
                            /"/g,
                            "&quot;"
                        );
                        return (
                            `<button class="btn btn-sm btn-outline-primary me-2 btn-edit" data-id="${data}" data-name="${name}" data-desc="${desc}">Edit</button>` +
                            `<button class="btn btn-sm btn-outline-danger btn-delete" data-id="${data}">Delete</button>`
                        );
                    },
                },
            ],
            responsive: true,
            drawCallback: function () {
                $("#selectAll").prop("checked", false);
                toggleBulkDeleteBtn();
            },
        });
    }

    function toggleBulkDeleteBtn() {
        var count = $(".item-checkbox:checked").length;
        if (count > 0) {
            $("#btnBulkDelete")
                .removeClass("d-none")
                .text(`Delete Selected (${count})`);
        } else {
            $("#btnBulkDelete").addClass("d-none");
        }
    }

    function openModal(title, data) {
        $("#itemModalLabel").text(title || "Add Item");
        $("#id_item").val((data && data.id_item) || "");
        $("#item_name").val((data && data.item_name) || "");

        // For edit, we populate the first item and remove others
        // But since the modal structure is dynamic, we need to handle it carefully.
        // The modaladd.blade.php uses .item-item .item_name

        // Reset modal first
        $("#multiInputs").find(".item-item:gt(0)").remove();
        var firstItem = $("#multiInputs .item-item").first();

        if (data) {
            firstItem.find(".item_name").val(data.item_name);
            // Hide addRow button for edit mode if we only support single edit
            // But typically the edit is single item.
        } else {
            firstItem.find(".item_name").val("");
        }

        var modal = new bootstrap.Modal(document.getElementById("itemModal"));
        modal.show();
    }

    function closeModal() {
        var el = document.getElementById("itemModal");
        var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        if (modal) {
            modal.hide();
            setTimeout(function () {
                if (bootstrap.Modal.getInstance(el))
                    bootstrap.Modal.getInstance(el).dispose();
                document.body.classList.remove("modal-open");
                document
                    .querySelectorAll(".modal-backdrop")
                    .forEach(function (b) {
                        b.remove();
                    });
                el.classList.remove("show");
                el.setAttribute("aria-hidden", "true");
                el.style.display = "none";
                $("#itemForm")[0].reset();
            }, 150);
        }
    }

    function collectItems() {
        var items = [];
        $("#multiInputs .item-item").each(function () {
            var name = $(this).find(".item_name").val().trim();
            if (name) items.push({ item_name: name });
        });
        return items;
    }

    $(document).ready(function () {
        initTable();

        // Select All checkbox
        $(document).on("change", "#selectAll", function () {
            $(".item-checkbox").prop("checked", $(this).prop("checked"));
            toggleBulkDeleteBtn();
        });

        // Individual checkbox
        $(document).on("change", ".item-checkbox", function () {
            var allChecked =
                $(".item-checkbox:checked").length ===
                $(".item-checkbox").length;
            $("#selectAll").prop("checked", allChecked);
            toggleBulkDeleteBtn();
        });

        // Bulk Delete Button
        $("#btnBulkDelete")
            .off("click")
            .on("click", function () {
                var ids = [];
                $(".item-checkbox:checked").each(function () {
                    ids.push($(this).val());
                });

                if (ids.length === 0) return;

                if (window.Swal) {
                    Swal.fire({
                        icon: "warning",
                        title: "Delete selected items?",
                        text: `You are about to delete ${ids.length} items.`,
                        showCancelButton: true,
                        cancelButtonText: "Cancel",
                        confirmButtonText: "Delete",
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            ajax({
                                url: "/items/bulk",
                                method: "DELETE",
                                data: { ids: ids },
                            }).done(function () {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Items Deleted",
                                        timer: 1200,
                                        showConfirmButton: false,
                                    });
                                }
                                if (table) table.ajax.reload();
                            });
                        }
                    });
                } else {
                    if (confirm(`Delete ${ids.length} selected items?`)) {
                        ajax({
                            url: "/items/bulk",
                            method: "DELETE",
                            data: { ids: ids },
                        }).done(function () {
                            if (table) table.ajax.reload();
                        });
                    }
                }
            });

        $("#btnAdd")
            .off("click")
            .on("click", function () {
                openModal("Add Item", null);
                $("#id_item").val("");
                $("#multiInputs").find(".item-item:gt(0)").remove();
                $("#multiInputs .item-item").find(".item_name").val("");
            });

        $("#saveItem")
            .off("click")
            .on("click", function () {
                var id = $("#id_item").val();
                if (id) {
                    var name = $("#multiInputs .item-item")
                        .first()
                        .find(".item_name")
                        .val()
                        .trim();

                    if (!name) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Item name is required",
                            });
                        return;
                    }
                    var req = ajax({
                        url: "/items/" + id,
                        method: "PUT",
                        data: { item_name: name },
                    });
                } else {
                    var items = collectItems();
                    if (items.length === 0) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Add at least one item",
                            });
                        return;
                    }
                    var invalid = false;
                    $("#multiInputs .item-item").each(function () {
                        var input = $(this).find(".item_name");
                        if (!input.val().trim()) {
                            input.addClass("is-invalid");
                            invalid = true;
                        } else {
                            input.removeClass("is-invalid");
                        }
                    });
                    if (invalid) return;
                    var req = ajax({
                        url: "/items/bulk",
                        method: "POST",
                        data: { items: items },
                    });
                }
                var $btn = $("#saveItem");
                $btn.prop("disabled", true);
                req.done(function () {
                    closeModal();
                    if (window.Swal) {
                        Swal.fire({
                            icon: "success",
                            title: id ? "Item updated" : "Items added",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    }
                    if (table) table.ajax.reload();
                }).always(function () {
                    $btn.prop("disabled", false);
                });
            });

        $("#addRow")
            .off("click")
            .on("click", function () {
                var last = $("#multiInputs .item-item").last();
                var clone = last.clone();
                clone.find(".item_name").val("").removeClass("is-invalid");
                $("#multiInputs").append(clone);
            });

        $(document).on("click", ".btn-remove-row", function () {
            var items = $("#multiInputs .item-item");
            if (items.length > 1) {
                $(this).closest(".item-item").remove();
            }
        });

        $(document).on("click", ".btn-edit", function () {
            var id = $(this).data("id");
            var name = $(this).data("name");
            var desc = $(this).data("desc");
            openModal("Edit Item", {
                id_item: id,
                item_name: name,
                description: desc,
            });
            $("#multiInputs").find(".item-item:gt(0)").remove();
            var firstItem = $("#multiInputs .item-item").first();
            firstItem.find(".item_name").val(name);
        });

        $(document).on("click", ".btn-delete", function () {
            var id = $(this).data("id");
            if (!id) return;
            if (window.Swal) {
                Swal.fire({
                    icon: "warning",
                    title: "Delete this item?",
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                    confirmButtonText: "Delete",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        ajax({ url: "/items/" + id, method: "DELETE" }).done(
                            function () {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Item Deleted",
                                        timer: 1200,
                                        showConfirmButton: false,
                                    });
                                }
                                if (table) table.ajax.reload();
                            }
                        );
                    }
                });
            } else {
                if (confirm("Delete this item?")) {
                    ajax({ url: "/items/" + id, method: "DELETE" }).done(
                        function () {
                            if (table) table.ajax.reload();
                        }
                    );
                }
            }
        });

        window.loadItems = initTable;
    });
})();
