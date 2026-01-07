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
    var myDropzone;

    // Disable auto discover for all elements:
    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;
    }

    function initTable() {
        if (!document.getElementById("table-problem")) return;

        var activeType =
            $(".nav-tabs .nav-link.active").data("type") || "manufacturing";
        var url = "/problems/list?type=" + encodeURIComponent(activeType);

        if ($.fn.DataTable.isDataTable("#table-problem")) {
            var dt = $("#table-problem").DataTable();
            dt.ajax.url(url).load();
            return;
        }

        table = $("#table-problem").DataTable({
            ajax: {
                url: url,
                dataSrc: "",
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    data: "created_at",
                    render: function (data) {
                        return data ? new Date(data).toLocaleDateString() : "-";
                    },
                },
                { data: "project", className: "text-uppercase" },
                { data: "kanban", className: "text-uppercase" },
                { data: "item" },
                { data: "location" },
                { data: "problem" },
                { data: "status" },
                {
                    data: "id_problem",
                    orderable: false,
                    render: function (data, type, row) {
                        let updateButton = "";

                        if (row.status === "dispatched") {
                            updateButton = `<button class="btn btn-sm btn-outline-primary btn-update-status" data-id="${data}" data-status="in_progress">In Progress</button>`;
                        } else if (row.status === "in_progress") {
                            updateButton = `<button class="btn btn-sm btn-outline-primary btn-update-status" data-id="${data}" data-status="closed">Closed</button>`;
                        }

                        return `
                        <button class="btn btn-sm btn-outline-info me-2 btn-detail" data-id="${data}"><i data-lucide="eye"></i></button>
                        <button class="btn btn-sm btn-outline-primary me-2 btn-excel" data-id="${data}"><i data-lucide="file-spreadsheet"></i></button>
                        <button class="btn btn-sm btn-outline-danger me-2 btn-p-delete" data-id="${data}"><i data-lucide="trash"></i></button>
                        ${updateButton}`;
                    },
                },
            ],
            responsive: true,
            drawCallback: function () {
                lucide.createIcons();
            },
        });
    }

    function openProblemModal() {
        $("#problemForm")[0].reset();
        if (myDropzone) {
            myDropzone.removeAllFiles(true);
        }
        $("#p_kanban")
            .empty()
            .append('<option value="">Select kanban</option>');
        // Explicitly reset select to manufacturing
        $('input[name="p_type"][value="manufacturing"]').prop("checked", true);

        // Reset UI to Select Mode
        $("#p_project").removeClass("d-none").val("");
        $("#new_project_name").addClass("d-none").val("");
        $("#toggleProjectMode").html('<i class="bi bi-plus-lg"></i> New');

        $("#p_kanban").removeClass("d-none").val("");
        $("#new_kanban_name").addClass("d-none").val("");
        $("#toggleKanbanMode")
            .html('<i class="bi bi-plus-lg"></i> New')
            .prop("disabled", false);

        $("#p_item_select").removeClass("d-none").val("");
        $("#new_item_name").addClass("d-none").val("");
        $("#toggleItemMode").html('<i class="bi bi-plus-lg"></i> New');

        var modal = new bootstrap.Modal(
            document.getElementById("problemModal")
        );
        modal.show();
    }

    $(document).ready(function () {
        initTable();
        
        // Initialize Dropzone
        if (document.getElementById("problem-dropzone") && typeof Dropzone !== 'undefined') {
            // Template for the file preview
            var previewTemplate = `
                <div class="card mt-1 mb-0 shadow-none border">
                    <div class="p-2">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="dz-image">
                                    <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                </div>
                            </div>
                            <div class="col ps-0">
                                <a href="javascript:void(0);" class="text-muted fw-bold" data-dz-name></a>
                                <p class="mb-0" data-dz-size></p>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-danger" data-dz-remove>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            myDropzone = new Dropzone("#problem-dropzone", {
                url: "/problems/store", // Not used for auto processing but required
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 10,
                maxFiles: 10,
                acceptedFiles: "image/*",
                previewTemplate: previewTemplate,
                previewsContainer: "#dropzone-preview",
                clickable: "#problem-dropzone", // Define the element that should trigger file selection
            });
        }

        // Handle tab click
        $(".nav-tabs .nav-link").on("shown.bs.tab", function (e) {
            initTable();
        });

        $("#btnProblemAdd")
            .off("click")
            .on("click", function () {
                openProblemModal();
            });

        $("#p_project")
            .off("change")
            .on("change", function () {
                var pid = $(this).val();
                $("#p_kanban")
                    .empty()
                    .append('<option value="">Select kanban</option>');
                if (!pid) return;
                ajax({
                    url: "/kanbans/list",
                    method: "GET",
                    data: { project_id: pid },
                }).done(function (items) {
                    items.forEach(function (k) {
                        $("#p_kanban").append(
                            '<option value="' +
                                k.id_kanban +
                                '">' +
                                k.kanban_name +
                                "</option>"
                        );
                    });
                });
            });

        // Toggle Project Mode
        $(document).on("click", "#toggleProjectMode", function () {
            var isSelect = !$("#p_project").hasClass("d-none");
            if (isSelect) {
                $("#p_project").addClass("d-none");
                $("#new_project_name").removeClass("d-none").focus();
                $(this).html('<i class="bi bi-x-lg"></i> Cancel');

                // If new project, force new kanban
                if (!$("#p_kanban").hasClass("d-none")) {
                    $("#toggleKanbanMode").trigger("click");
                }
                $("#toggleKanbanMode").prop("disabled", true); // Cannot switch back to select kanban if new project
            } else {
                $("#p_project").removeClass("d-none");
                $("#new_project_name").addClass("d-none").val("");
                $(this).html('<i class="bi bi-plus-lg"></i> New');

                $("#toggleKanbanMode").prop("disabled", false);
            }
        });

        // Toggle Kanban Mode
        $(document).on("click", "#toggleKanbanMode", function () {
            var isSelect = !$("#p_kanban").hasClass("d-none");
            if (isSelect) {
                $("#p_kanban").addClass("d-none");
                $("#new_kanban_name").removeClass("d-none").focus();
                $(this).html('<i class="bi bi-x-lg"></i> Cancel');
            } else {
                $("#p_kanban").removeClass("d-none");
                $("#new_kanban_name").addClass("d-none").val("");
                $(this).html('<i class="bi bi-plus-lg"></i> New');
            }
        });

        // Toggle Item Mode
        $(document).on("click", "#toggleItemMode", function () {
            var isSelect = !$("#p_item_select").hasClass("d-none");
            if (isSelect) {
                $("#p_item_select").addClass("d-none");
                $("#new_item_name").removeClass("d-none").focus();
                $(this).html('<i class="bi bi-x-lg"></i> Cancel');
            } else {
                $("#p_item_select").removeClass("d-none");
                $("#new_item_name").addClass("d-none").val("");
                $(this).html('<i class="bi bi-plus-lg"></i> New');
            }
        });

        $("#saveProblem")
            .off("click")
            .on("click", function (e) {
                e.preventDefault();
                var projectId = $("#p_project").val();
                var kanbanId = $("#p_kanban").val();

                // Item logic
                var itemId = $("#p_item_select").val();
                var newItemName = $("#new_item_name").val().trim();

                var newProjectName = $("#new_project_name").val().trim();
                var newKanbanName = $("#new_kanban_name").val().trim();

                var locationId = $("#p_location").val();
                var type = $('input[name="p_type"]:checked').val();
                if (!type) {
                    type = "manufacturing"; // Fallback if undefined
                }
                var problem = $("#p_problem").val().trim();
                var cause = $("#p_cause").val().trim();
                var curative = $("#p_curative").val().trim();

                var formData = new FormData();

                // Append Dropzone files
                if (myDropzone) {
                    var files = myDropzone.files;
                    for (var i = 0; i < files.length; i++) {
                        formData.append("attachment[]", files[i]);
                    }
                }
                if (!$("#p_project").hasClass("d-none")) {
                    if (!projectId) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Select a project",
                            });
                        else alert("Select a project");
                        return;
                    }
                    formData.append("id_project", projectId);
                } else {
                    if (!newProjectName) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Enter new project name",
                            });
                        else alert("Enter new project name");
                        return;
                    }
                    formData.append("new_project_name", newProjectName);
                }

                // Kanban Logic
                if (!$("#p_kanban").hasClass("d-none")) {
                    // Only required if we are not creating a new project
                    if (!$("#p_project").hasClass("d-none") && !kanbanId) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Select a kanban",
                            });
                        else alert("Select a kanban");
                        return;
                    }
                    if (kanbanId) formData.append("id_kanban", kanbanId);
                } else {
                    if (!newKanbanName) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Enter new kanban name",
                            });
                        else alert("Enter new kanban name");
                        return;
                    }
                    formData.append("new_kanban_name", newKanbanName);
                }

                // Item Logic
                if (!$("#p_item_select").hasClass("d-none")) {
                    if (!itemId) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Select an item",
                            });
                        else alert("Select an item");
                        return;
                    }
                    formData.append("id_item", itemId);
                } else {
                    if (!newItemName) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Enter new item name",
                            });
                        else alert("Enter new item name");
                        return;
                    }
                    formData.append("new_item_name", newItemName);
                }

                if (!locationId) {
                    if (window.Swal)
                        Swal.fire({
                            icon: "error",
                            title: "Select a location",
                        });
                    else alert("Select a location");
                    return;
                }
                if (!problem) {
                    Swal.fire({
                        icon: "error",
                        title: "Problem description is required",
                    });
                    return;
                }
                if (!cause) {
                    Swal.fire({
                        icon: "error",
                        title: "Root cause is required",
                    });
                    return;
                }
                if (!curative) {
                    Swal.fire({
                        icon: "error",
                        title: "Curative action is required",
                    });
                    return;
                }

                formData.append("id_location", locationId);
                formData.append("type", type);
                formData.append("problem", problem);
                formData.append("cause", cause);
                formData.append("curative", curative);

                var $btn = $("#saveProblem");
                $btn.prop("disabled", true);
                $.ajax({
                    url: "/problems/store",
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf(),
                        Accept: "application/json",
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    error: function (xhr) {
                        var msg =
                            (xhr.responseJSON && xhr.responseJSON.message) ||
                            "An error occurred";
                        // Handle validation errors specifically
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = [];
                            for (var key in errors) {
                                errorMessages.push(errors[key][0]);
                            }
                            msg = errorMessages.join("<br>");
                        }

                        if (window.Swal) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                html: msg,
                            });
                        } else {
                            alert(msg.replace(/<br>/g, "\n"));
                        }
                    },
                })
                    .done(function () {
                        var el = document.getElementById("problemModal");
                        var modal = bootstrap.Modal.getInstance(el);
                        if (modal) modal.hide();
                        if (window.Swal)
                            Swal.fire({
                                icon: "success",
                                title: "Problem added",
                                timer: 1500,
                                showConfirmButton: false,
                            });

                        // Reset inputs
                        $("#p_project").removeClass("d-none");
                        $("#new_project_name").addClass("d-none").val("");
                        $("#toggleProjectMode").html(
                            '<i class="bi bi-plus-lg"></i> New'
                        );

                        $("#p_kanban").removeClass("d-none");
                        $("#new_kanban_name").addClass("d-none").val("");
                        $("#toggleKanbanMode")
                            .html('<i class="bi bi-plus-lg"></i> New')
                            .prop("disabled", false);

                        // Reload project dropdown just in case
                        // Ideally we should reload the whole page or fetch projects again, but let's just reload table
                        if (table) table.ajax.reload();
                    })
                    .always(function () {
                        $btn.prop("disabled", false);
                    });
            });

        $(document).on("click", ".btn-p-delete", function () {
            var id = $(this).data("id");
            if (window.Swal) {
                Swal.fire({
                    icon: "warning",
                    title: "Delete this problem?",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel",
                }).then(function (result) {
                    if (result.isConfirmed) {
                        ajax({ url: "/problems/" + id, method: "DELETE" }).done(
                            function () {
                                if (window.Swal)
                                    Swal.fire({
                                        icon: "success",
                                        title: "Deleted",
                                        timer: 1200,
                                        showConfirmButton: false,
                                    });
                                if (table) table.ajax.reload();
                            }
                        );
                    }
                });
            } else {
                if (confirm("Delete this problem?")) {
                    ajax({ url: "/problems/" + id, method: "DELETE" }).done(
                        function () {
                            if (table) table.ajax.reload();
                        }
                    );
                }
            }
        });

        $(document).on("click", ".btn-detail", function () {
            var id = $(this).data("id");
            var tr = $(this).closest("tr");
            var row = table.row(tr).data();

            if (!row) {
                // Fallback for edge cases
                var allData = table.rows().data().toArray();
                row = allData.find(function (item) {
                    return item.id_problem == id;
                });
            }

            if (!row) return;

            // Populate details fields (unchanged)
            $("#d_project").val(row.project || "");
            $("#d_kanban").val(row.kanban || "");
            $("#d_item").val(row.item || "-");
            $("#d_location").val(row.location || "-");
            $("#d_type").val(row.type || "-");
            $("#d_status").val(row.status || "-");
            $("#d_reporter").val(row.reporter || "-");
            $("#d_created_at").val(
                row.created_at
                    ? new Date(row.created_at).toLocaleDateString()
                    : "-"
            );
            $("#d_problem").val(row.problem || "-");
            $("#d_cause").val(row.cause || "-");
            $("#d_curative").val(row.curative || "-");

            // Generate carousel for attachments
            var attachments = [];
            if (
                row.attachments &&
                Array.isArray(row.attachments) &&
                row.attachments.length > 0
            ) {
                attachments = row.attachments;
            } else if (row.attachment) {
                attachments = [row.attachment]; // Treat single as array
            }

            var carouselHtml = "";
            if (attachments.length > 0) {
                var carouselId = "attachmentCarousel";
                carouselHtml = `<div id="${carouselId}" class="carousel slide" data-bs-ride="carousel">`;
                carouselHtml += `<div class="carousel-indicators">`;

                // Add indicators
                attachments.forEach(function (_, index) {
                    var activeClass = index === 0 ? "active" : "";
                    carouselHtml += `<button type="button" data-bs-target="#${carouselId}" data-bs-slide-to="${index}" class="${activeClass}" aria-label="Slide ${
                        index + 1
                    }"></button>`;
                });

                carouselHtml += `</div><div class="carousel-inner">`;

                attachments.forEach(function (path, index) {
                    var activeClass = index === 0 ? "active" : "";
                    carouselHtml += `
                <div class="carousel-item ${activeClass}">
                    <a href="/storage/${path}" target="_blank" class="d-block">
                        <img src="/storage/${path}" class="d-block w-100 rounded shadow-sm" alt="Attachment ${
                        index + 1
                    }" style="max-height: 300px; object-fit: cover; transition: transform 0.2s;">
                    </a>
                </div>
            `;
                });

                carouselHtml += `</div>`;

                // Add controls only if multiple images
                if (attachments.length > 1) {
                    carouselHtml += `
                <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            `;
                }

                carouselHtml += `</div>`;
            } else {
                carouselHtml = `<div class="text-center text-muted">No attachments available</div>`;
            }

            // Insert into modal
            $("#attachment-carousel").html(carouselHtml);

            // Show modal
            var modal = new bootstrap.Modal(
                document.getElementById("problemDetailModal")
            );
            modal.show();
        });

        $(document).on("click", ".btn-excel", function () {
            var id = $(this).data("id");
            if (!id) return;

            var btn = $(this);
            var originalText = btn.html();
            btn.prop("disabled", true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
            );

            fetch("/problems/" + id + "/export", {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrf(),
                },
            })
                .then((response) => {
                    if (response.ok) {
                        return response.blob().then((blob) => {
                            var url = window.URL.createObjectURL(blob);
                            var a = document.createElement("a");
                            a.href = url;
                            // Try to get filename from header
                            var filename = "problem_" + id + ".xlsx";
                            var disposition = response.headers.get(
                                "Content-Disposition"
                            );
                            if (
                                disposition &&
                                disposition.indexOf("attachment") !== -1
                            ) {
                                var filenameRegex =
                                    /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                                var matches = filenameRegex.exec(disposition);
                                if (matches != null && matches[1]) {
                                    filename = matches[1].replace(/['"]/g, "");
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
                        return response
                            .json()
                            .then((data) => {
                                throw new Error(
                                    data.message || "Error exporting Excel"
                                );
                            })
                            .catch((err) => {
                                // If json parse fails, use status text
                                throw new Error(
                                    "Export failed: " + response.statusText
                                );
                            });
                    }
                })
                .catch((error) => {
                    if (window.Swal) {
                        Swal.fire({
                            icon: "error",
                            title: "Export Failed",
                            text: error.message,
                        });
                    } else {
                        alert(error.message);
                    }
                })
                .finally(() => {
                    btn.prop("disabled", false).html(originalText);
                });
        });

        $(document).on("click", ".btn-update-status", function () {
            var problemId = $(this).data("id");
            var currentStatus = $(this).data("status");
            var $btn = $(this);

            var statusLabel = currentStatus
                .replace("_", " ")
                .replace(/\b\w/g, (l) => l.toUpperCase());

            if (window.Swal) {
                Swal.fire({
                    title: "Update Status?",
                    text:
                        "Are you sure you want to update status to " +
                        statusLabel +
                        "?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, update it!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        performUpdate();
                    }
                });
            } else {
                if (
                    confirm(
                        "Are you sure you want to update status to " +
                            statusLabel +
                            "?"
                    )
                ) {
                    performUpdate();
                }
            }

            function performUpdate() {
                ajax({
                    url: "/update-status/" + problemId,
                    method: "POST",
                    data: {
                        status: currentStatus,
                    },
                }).done(function (response) {
                    if (response.success) {
                        if (currentStatus === "in_progress") {
                            $btn.text("Update to Closed").data(
                                "status",
                                "closed"
                            );
                        } else if (currentStatus === "closed") {
                            $btn.remove();
                        } else if (currentStatus === "dispatched") {
                            $btn.text("Update to In Progress").data(
                                "status",
                                "in_progress"
                            );
                        }
                        if (table) table.ajax.reload(null, false);

                        if (window.Swal) {
                            Swal.fire({
                                icon: "success",
                                title: "Updated!",
                                text: "Status has been updated.",
                                timer: 1500,
                                showConfirmButton: false,
                            });
                        }
                    } else {
                        if (window.Swal) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Error updating status.",
                            });
                        } else {
                            alert("Error updating status.");
                        }
                    }
                });
            }
        });

        window.loadProblems = initTable;
    });
})();
