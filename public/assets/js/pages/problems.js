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
    var myDropzone;
    var fltProjectChoices, fltKanbanChoices, fltGroupCodeChoices;
    var currentFilter = {};

    function currentProblemType() {
        var t = $('input[name="p_type"]:checked').val();
        return t || "";
    }

    function typeShort(type) {
        var map = {
            manufacturing: "MFG",
            kentokai: "KTC",
            ks: "KS",
            kd: "KD",
            sk: "SK",
            buyoff: "BO",
        };
        type = (type || "").toLowerCase();
        return map[type] || type.toUpperCase().slice(0, 3);
    }

    function normalizeForCode(name) {
        name = (name || "").toString().trim().toUpperCase();
        name = name.replace(/\s+/g, " ");
        name = name.replace(/ /g, "-");
        name = name.replace(/[^A-Z0-9\-]/g, "");
        return name;
    }

    function buildGroupCodePrefix() {
        var type = currentProblemType();
        var projectText = $("#p_project option:selected").text() || "";
        var kanbanText = $("#p_kanban option:selected").text() || "";
        if (!type || !projectText || !kanbanText) return "";
        var t = typeShort(type);
        var p = normalizeForCode(projectText);
        var k = normalizeForCode(kanbanText);
        return t + "_" + p + "_" + k + "_";
    }

    function buildModalGroupCodePrefix() {
        var type = $("#d_type").val();
        var projectText = $("#d_project option:selected").text() || "";
        var kanbanText = $("#d_kanban option:selected").text() || "";
        if (!type || !projectText || !kanbanText) return "";
        var t = typeShort(type);
        var p = normalizeForCode(projectText);
        var k = normalizeForCode(kanbanText);
        return t + "_" + p + "_" + k + "_";
    }

    function setTypeDisabled(disabled) {
        $('input[name="p_type"]').prop("disabled", disabled);
    }

    function updateGroupCodePreview() {
        var mode = $("#group_code_mode").val();
        var selVal = $("#group_code_select").val();
        var suffix = $("#group_code_suffix").val().trim();
        var prefix = buildGroupCodePrefix();
        var text = "";
        if (mode === "existing" && selVal && selVal !== "__new__") {
            text = selVal;
        } else if (mode === "new") {
            text = prefix + suffix;
        } else if (prefix) {
            text = prefix + "...";
        }
        $("#group_code_preview").text(text);
    }

    function resetGroupCodeUI() {
        $("#group_code_select").val("");
        $("#group_code_suffix").val("").addClass("d-none");
        $("#group_code_mode").val("");
        $("#group_code").val("");
        $("#group_code_preview").text("");
        setTypeDisabled(false);
    }

    function reloadProblemCodeOptions() {
        var type = currentProblemType();
        var projectId = $("#p_project").val();
        var kanbanId = $("#p_kanban").val();
        var $sel = $("#group_code_select");
        if (!$sel.length) {
            return;
        }
        if (!type || !projectId || !kanbanId) {
            $sel.empty();
            $sel.append('<option value="">Select existing code</option>');
            $sel.append('<option value="__new__">+ New Code</option>');
            updateGroupCodePreview();
            return;
        }
        var url =
            "/api/problem-codes?type=" +
            encodeURIComponent(type) +
            "&id_project=" +
            encodeURIComponent(projectId) +
            "&id_kanban=" +
            encodeURIComponent(kanbanId);
        $sel.prop("disabled", true);
        $.getJSON(url)
            .done(function (data) {
                console.log("problem-codes params", {
                    type: type,
                    id_project: projectId,
                    id_kanban: kanbanId,
                });
                console.log("problem-codes response", data);
                $sel.empty();
                $sel.append('<option value="">Select existing code</option>');
                (data || []).forEach(function (row) {
                    if (row.code) {
                        $sel.append(
                            '<option value="' +
                                row.code +
                                '">' +
                                row.code +
                                "</option>",
                        );
                    }
                });
                $sel.append('<option value="__new__">+ New Code</option>');
            })
            .always(function () {
                $sel.prop("disabled", false);
                updateGroupCodePreview();
            });
    }

    // Disable auto discover for all elements:
    if (typeof Dropzone !== "undefined") {
        Dropzone.autoDiscover = false;
    }

    function toggleGroupCodeVisibility() {
        var type = currentProblemType();
        var isMfg = (type === 'manufacturing');
        // Find the container for problem code inputs
        // The structure is col-md-12 -> mb-3 -> label + row(inputs)
        // We can find it via one of the inputs
        var $container = $("#group_code_select").closest('.col-md-12');
        
        if (isMfg) {
            $container.addClass('d-none');
        } else {
            $container.removeClass('d-none');
        }
    }

    function initTable() {
        if (!document.getElementById("table-problem")) return;

        var activeType =
            $(".nav-tabs .nav-link.active").data("type") || "manufacturing";
        var url = "/problems/list?type=" + encodeURIComponent(activeType);

        if (currentFilter.project_id) url += "&project_id=" + encodeURIComponent(currentFilter.project_id);
        if (currentFilter.kanban_id) url += "&kanban_id=" + encodeURIComponent(currentFilter.kanban_id);
        if (currentFilter.group_code) url += "&group_code=" + encodeURIComponent(currentFilter.group_code);
        if (currentFilter.start_date) url += "&start_date=" + encodeURIComponent(currentFilter.start_date);
        if (currentFilter.end_date) url += "&end_date=" + encodeURIComponent(currentFilter.end_date);

        // Logic: Manufacturing -> Use DataTables Excel Button (In Table)
        // Others -> Hide DataTables Excel Button
        var isMfg = activeType === "manufacturing";
        $("#btnFilterTable").show(); // Always show the main Filter button

        if ($.fn.DataTable.isDataTable("#table-problem")) {
            var dt = $("#table-problem").DataTable();
            dt.ajax.url(url).load();
            
            // Toggle DataTables Buttons
            if (dt.buttons) {
                if (isMfg) {
                    dt.buttons().container().show();
                } else {
                    dt.buttons().container().hide();
                }
            }
            return;
        }

        table = $("#table-problem").DataTable({
            dom: "Blfrtip",
            lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            buttons: [
                {
                    extend: "excelHtml5",
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    className: "btn btn-success btn-sm mb-3",
                    exportOptions: {
                        columns: ":not(:last-child)", // Exclude actions column
                    },
                },
            ],
            ajax: {
                url: url,
                dataSrc: "",
            },
            initComplete: function () {
                var dt = this.api();
                if (dt.buttons) {
                    if (isMfg) {
                        dt.buttons().container().show();
                    } else {
                        dt.buttons().container().hide();
                    }
                }
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

                        if (row.status === "in_progress") {
                            updateButton = `<button class="btn btn-sm btn-outline-primary btn-update-status" data-id="${data}" data-status="dispatched">Dispatched</button>`;
                        } else if (row.status === "dispatched") {
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
            scrollX: true,
            scrollY: "60vh",
            scrollCollapse: true,
            // responsive: true,
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

        var activeType =
            $(".nav-tabs .nav-link.active").data("type") || "manufacturing";
        $('input[name="p_type"][value="' + activeType + '"]').prop(
            "checked",
            true,
        );

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

        resetGroupCodeUI();
        reloadProblemCodeOptions();
        toggleGroupCodeVisibility();

        var modal = new bootstrap.Modal(
            document.getElementById("problemModal"),
        );
        modal.show();
    }

    $(document).ready(function () {
        initTable();

        // Init Choices for Filter Modal
        if (typeof Choices !== "undefined") {
            var choicesOpts = {
                searchEnabled: true,
                itemSelectText: "",
                shouldSort: false,
                placeholder: true,
                placeholderValue: "Select Option",
            };
            if (document.getElementById("flt_project")) {
                fltProjectChoices = new Choices("#flt_project", choicesOpts);
            }
            if (document.getElementById("flt_kanban")) {
                fltKanbanChoices = new Choices("#flt_kanban", choicesOpts);
            }
            if (document.getElementById("flt_group_code")) {
                fltGroupCodeChoices = new Choices(
                    "#flt_group_code",
                    choicesOpts,
                );
            }
        }

        // Initialize Dropzone
        if (
            document.getElementById("problem-dropzone") &&
            typeof Dropzone !== "undefined"
        ) {
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

        $(".nav-tabs .nav-link").on("shown.bs.tab", function (e) {
            initTable();
        });

        // Filter Table Handler (Opens Modal)
        $("#btnFilterTable")
            .off("click")
            .on("click", function () {
                var activeType = $(".nav-tabs .nav-link.active").data("type") || "manufacturing";

                // Set the type in the modal and disable it (context only)
                $("#flt_type").val(activeType).prop('disabled', true);

                // Set Dates
                $("#flt_start_date").val(currentFilter.start_date || "");
                $("#flt_end_date").val(currentFilter.end_date || "");

                // Reset logic for dependent selects
                // We clear and reload based on currentFilter or empty
                
                if (fltProjectChoices) fltProjectChoices.setChoiceByValue(currentFilter.project_id ? currentFilter.project_id.toString() : "");
                else $("#flt_project").val(currentFilter.project_id || "");

                // Clear downstream
                if (fltKanbanChoices) {
                    fltKanbanChoices.clearChoices();
                    fltKanbanChoices.setChoices([{ value: "", label: "All Kanbans", selected: true, hidden: true }], "value", "label", true);
                } else {
                    $("#flt_kanban").html('<option value="">All Kanbans</option>');
                }

                if (fltGroupCodeChoices) {
                    fltGroupCodeChoices.clearChoices();
                    fltGroupCodeChoices.setChoices([{ value: "", label: "Select Group Code", selected: true }], "value", "label", true);
                } else {
                    $("#flt_group_code").html('<option value="">Select Group Code</option>');
                }

                // If we have a project, we need to load kanbans
                var projectId = $("#flt_project").val();
                if (projectId) {
                    ajax({
                        url: "/kanbans/list",
                        method: "GET",
                        data: { project_id: projectId },
                    }).done(function (items) {
                        if (fltKanbanChoices) {
                            var choices = [{ value: "", label: "All Kanbans", selected: true }];
                            items.forEach(function (k) {
                                choices.push({ value: k.id_kanban, label: k.kanban_name });
                            });
                            fltKanbanChoices.setChoices(choices, "value", "label", true);
                            // Restore selected kanban
                            if (currentFilter.kanban_id) fltKanbanChoices.setChoiceByValue(currentFilter.kanban_id.toString());
                        } else {
                            items.forEach(function (k) {
                                $("#flt_kanban").append('<option value="' + k.id_kanban + '">' + k.kanban_name + "</option>");
                            });
                            $("#flt_kanban").val(currentFilter.kanban_id || "");
                        }

                        // If we have kanban, load group codes
                        var kanbanId = $("#flt_kanban").val();
                        if (kanbanId) {
                            loadGroupCodes(activeType, projectId, kanbanId);
                        }
                    });
                }

                var modal = new bootstrap.Modal(document.getElementById("filterModal"));
                modal.show();
            });

        // Helper to load group codes
        function loadGroupCodes(type, projectId, kanbanId) {
             if (!type || !projectId || !kanbanId) return;
             
             var url = "/api/problem-codes?type=" + encodeURIComponent(type) +
                "&id_project=" + encodeURIComponent(projectId) +
                "&id_kanban=" + encodeURIComponent(kanbanId);

            $.getJSON(url).done(function (data) {
                if (fltGroupCodeChoices) {
                    var choices = [{ value: "", label: "Select Group Code", selected: true }];
                    (data || []).forEach(function (row) {
                        if (row.code) choices.push({ value: row.code, label: row.code });
                    });
                    fltGroupCodeChoices.setChoices(choices, "value", "label", true);
                    if (currentFilter.group_code) fltGroupCodeChoices.setChoiceByValue(currentFilter.group_code);
                } else {
                    (data || []).forEach(function (row) {
                        if (row.code) $("#flt_group_code").append('<option value="' + row.code + '">' + row.code + "</option>");
                    });
                    $("#flt_group_code").val(currentFilter.group_code || "");
                }
            });
        }

        // Filter Modal: Project Change
        $("#flt_project").off("change").on("change", function () {
            var projectId = $(this).val();

            // Reset Kanban
            if (fltKanbanChoices) {
                fltKanbanChoices.clearChoices();
                fltKanbanChoices.setChoices([{ value: "", label: "All Kanbans", selected: true }], "value", "label", true);
            } else {
                $("#flt_kanban").html('<option value="">All Kanbans</option>');
            }

            // Reset Group Code
            if (fltGroupCodeChoices) {
                fltGroupCodeChoices.clearChoices();
                fltGroupCodeChoices.setChoices([{ value: "", label: "Select Group Code", selected: true }], "value", "label", true);
            } else {
                $("#flt_group_code").html('<option value="">Select Group Code</option>');
            }

            if (projectId) {
                ajax({
                    url: "/kanbans/list",
                    method: "GET",
                    data: { project_id: projectId },
                }).done(function (items) {
                    if (fltKanbanChoices) {
                        var choices = [{ value: "", label: "All Kanbans", selected: true }];
                        items.forEach(function (k) {
                            choices.push({ value: k.id_kanban, label: k.kanban_name });
                        });
                        fltKanbanChoices.setChoices(choices, "value", "label", true);
                    } else {
                        items.forEach(function (k) {
                            $("#flt_kanban").append('<option value="' + k.id_kanban + '">' + k.kanban_name + "</option>");
                        });
                    }
                });
            }
        });

        // Filter Modal: Kanban Change -> Load Group Codes
        $("#flt_kanban").off("change").on("change", function () {
            var type = $("#flt_type").val();
            var projectId = $("#flt_project").val();
            var kanbanId = $(this).val();

            // Reset Group Code
            if (fltGroupCodeChoices) {
                fltGroupCodeChoices.clearChoices();
                fltGroupCodeChoices.setChoices([{ value: "", label: "Select Group Code", selected: true }], "value", "label", true);
            } else {
                $("#flt_group_code").html('<option value="">Select Group Code</option>');
            }

            if (type && projectId && kanbanId) {
                loadGroupCodes(type, projectId, kanbanId);
            }
        });

        // Reset Filter Logic
        $("#btnResetFilter").off("click").on("click", function () {
            // Reset currentFilter object
            currentFilter = {};

            // Reset UI Inputs
            if (fltProjectChoices) {
                fltProjectChoices.setChoiceByValue("");
            } else {
                $("#flt_project").val("");
            }

            // Reset Kanban
            if (fltKanbanChoices) {
                fltKanbanChoices.clearChoices();
                fltKanbanChoices.setChoices([{ value: "", label: "All Kanbans", selected: true }], "value", "label", true);
            } else {
                $("#flt_kanban").html('<option value="">All Kanbans</option>');
            }

            // Reset Group Code
            if (fltGroupCodeChoices) {
                fltGroupCodeChoices.clearChoices();
                fltGroupCodeChoices.setChoices([{ value: "", label: "Select Group Code", selected: true }], "value", "label", true);
            } else {
                $("#flt_group_code").html('<option value="">Select Group Code</option>');
            }

            // Reset Dates
            $("#flt_start_date").val("");
            $("#flt_end_date").val("");

            // Reload Table
            initTable();

            // Close modal
            var modalEl = document.getElementById("filterModal");
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        });

        // Apply Filter Logic
        $("#btnApplyFilter").off("click").on("click", function () {
            currentFilter.project_id = $("#flt_project").val();
            currentFilter.kanban_id = $("#flt_kanban").val();
            currentFilter.group_code = $("#flt_group_code").val();
            currentFilter.start_date = $("#flt_start_date").val();
            currentFilter.end_date = $("#flt_end_date").val();

            initTable();

            // Close modal
            var modalEl = document.getElementById("filterModal");
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
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
                if (!pid) {
                    reloadProblemCodeOptions();
                    return;
                }
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
                                "</option>",
                        );
                    });
                    reloadProblemCodeOptions();
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

        $(document).on("click", "#toggleKanbanMode", function () {
            var isSelect = !$("#p_kanban").hasClass("d-none");
            if (isSelect) {
                $("#p_kanban").addClass("d-none");
                $("#new_kanban_name").removeClass("d-none").focus();
                $(this).html('<i class="bi bi-x-lg"></i> Cancel');
                $("#group_code_select").empty();
                $("#group_code_select").append(
                    '<option value="">Select existing code</option>',
                );
                $("#group_code_select").append(
                    '<option value="__new__">+ New Code</option>',
                );
                resetGroupCodeUI();
            } else {
                $("#p_kanban").removeClass("d-none");
                $("#new_kanban_name").addClass("d-none").val("");
                $(this).html('<i class="bi bi-plus-lg"></i> New');
                reloadProblemCodeOptions();
            }
        });

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

        $(document).on("change", 'input[name="p_type"]', function () {
            reloadProblemCodeOptions();
            toggleGroupCodeVisibility();
        });

        $(document)
            .on("change", "#group_code_select", function () {
                var val = $(this).val();
                if (val === "__new__") {
                    $("#group_code_mode").val("new");
                    $("#group_code_suffix")
                        .removeClass("d-none")
                        .val("")
                        .focus();
                    $("#group_code").val("");
                    setTypeDisabled(false);
                } else if (val) {
                    $("#group_code_mode").val("existing");
                    $("#group_code_suffix").addClass("d-none").val("");
                    $("#group_code").val(val);
                    setTypeDisabled(true);
                } else {
                    $("#group_code_mode").val("");
                    $("#group_code_suffix").addClass("d-none").val("");
                    $("#group_code").val("");
                    setTypeDisabled(false);
                }
                updateGroupCodePreview();
            })
            .on("input", "#group_code_suffix", function () {
                var suffix = $(this).val().trim();
                if (suffix.length > 0) {
                    $("#group_code_mode").val("new");
                    var prefix = buildGroupCodePrefix();
                    $("#group_code").val(prefix + suffix);
                    setTypeDisabled(true);
                } else {
                    $("#group_code_mode").val("");
                    $("#group_code").val("");
                    setTypeDisabled(false);
                }
                updateGroupCodePreview();
            })
            .on("click", "#group_code_clear", function () {
                resetGroupCodeUI();
                reloadProblemCodeOptions();
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
                    type = "manufacturing";
                }
                var problem = $("#p_problem").val().trim();
                var cause = $("#p_cause").val().trim();
                var curative = $("#p_curative").val().trim();
                var preventive = $("#p_preventive").val();

                var machineId = $("#p_machine").val();
                var typeSaibo = $("#p_type_saibo").val();
                var classification = $("#p_classification").val();
                var stage = $("#p_stage").val();
                var seksiInCharge = $("#p_seksi_in_charge").val();
                var pic = $("#p_pic").val();
                var hour = $("#p_hour").val();

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

                var groupMode = $("#group_code_mode").val();
                var groupSelectVal = $("#group_code_select").val();
                var groupSuffix = $("#group_code_suffix").val().trim();
                var fullCode = $("#group_code").val().trim();

                if (type !== 'manufacturing') {
                    if (!groupMode) {
                        if (window.Swal)
                            Swal.fire({
                                icon: "error",
                                title: "Problem Code is required",
                            });
                        else alert("Problem Code is required");
                        return;
                    }

                    if (groupMode === "existing") {
                        if (!groupSelectVal || groupSelectVal === "__new__") {
                            if (window.Swal)
                                Swal.fire({
                                    icon: "error",
                                    title: "Please select existing Problem Code",
                                });
                            else alert("Please select existing Problem Code");
                            return;
                        }
                        if (!fullCode) {
                            fullCode = groupSelectVal;
                            $("#group_code").val(fullCode);
                        }
                        formData.append("group_code_mode", "existing");
                        formData.append("group_code_existing", groupSelectVal);
                    } else if (groupMode === "new") {
                        if (!groupSuffix) {
                            if (window.Swal)
                                Swal.fire({
                                    icon: "error",
                                    title: "Please input suffix for new Problem Code",
                                });
                            else alert("Please input suffix for new Problem Code");
                            return;
                        }
                        if (!fullCode) {
                            var prefix = buildGroupCodePrefix();
                            fullCode = prefix + groupSuffix;
                            $("#group_code").val(fullCode);
                        }
                        formData.append("group_code_mode", "new");
                        formData.append("group_code_suffix", groupSuffix);
                    }
                }

                if (fullCode) {
                    formData.append("group_code", fullCode);
                }

                formData.append("id_location", locationId);
                formData.append("type", type);
                formData.append("problem", problem);
                formData.append("cause", cause);
                formData.append("curative", curative);
                formData.append("preventive", preventive);
                if (machineId) formData.append("id_machine", machineId);
                if (typeSaibo) formData.append("type_saibo", typeSaibo);
                if (classification) formData.append("classification", classification);
                if (stage) formData.append("stage", stage);
                if (seksiInCharge) formData.append("id_seksi_in_charge", seksiInCharge);
                if (pic) formData.append("id_pic", pic);
                if (hour) formData.append("hour", hour);

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
                            '<i class="bi bi-plus-lg"></i> New',
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
                            },
                        );
                    }
                });
            } else {
                if (confirm("Delete this problem?")) {
                    ajax({ url: "/problems/" + id, method: "DELETE" }).done(
                        function () {
                            if (table) table.ajax.reload();
                        },
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

            // Populate details fields
            $("#d_project").val(row.id_project || "");

            // Kanban population
            var $kanbanSelect = $("#d_kanban");
            $kanbanSelect
                .empty()
                .append('<option value="">Select kanban</option>');
            if (row.id_project) {
                // Pre-add current kanban to avoid delay
                if (row.id_kanban) {
                    $kanbanSelect.append(
                        `<option value="${row.id_kanban}" selected>${row.kanban}</option>`,
                    );
                }
                // Fetch full list
                ajax({
                    url: "/kanbans/list",
                    method: "GET",
                    data: { project_id: row.id_project },
                }).done(function (items) {
                    // Clear again to avoid duplicates if we just appended current
                    $kanbanSelect
                        .empty()
                        .append('<option value="">Select kanban</option>');
                    items.forEach(function (k) {
                        var selected =
                            k.id_kanban == row.id_kanban ? "selected" : "";
                        $kanbanSelect.append(
                            `<option value="${k.id_kanban}" ${selected}>${k.kanban_name}</option>`,
                        );
                    });
                });
            }

            $("#d_item").val(row.id_item || "");
            $("#d_location").val(row.id_location || "");
            $("#d_machine").val(row.id_machine || "");
            $("#d_type_saibo").val(row.type_saibo || "");
            $("#d_classification").val(row.classification || "");
            $("#d_stage").val(row.stage || "");
            $("#d_seksi_in_charge").val(row.id_seksi_in_charge || "");
            $("#d_pic").val(row.id_pic || "");
            $("#d_hour").val(row.hour || "");
            $("#d_type").val(row.raw_type || "manufacturing");
            $("#d_status").val(row.status || "in_progress");
            $("#d_reporter").val(row.reporter || "-");
            $("#d_created_at").val(
                row.created_at
                    ? new Date(row.created_at).toLocaleDateString()
                    : "-",
            );
            $("#d_problem").val(row.problem || "");
            $("#d_cause").val(row.cause || "");
            $("#d_curative").val(row.curative || "");
            $("#d_preventive").val(row.preventive || "");

            // Group Code
            $("#d_group_code").val(row.group_code || "");
            $("#d_group_code_container").removeClass("d-none");
            $("#d_group_code_edit_container").addClass("d-none");
            $("#d_gc_suffix").val("");
            $("#d_gc_prefix").text("");

            // Reset UI state
            $("#btn-save-problem").data("id", id).addClass("d-none");
            $("#btn-edit-problem").removeClass("d-none");
            $(
                "#d_project, #d_kanban, #d_item, #d_location, #d_machine, #d_type_saibo, #d_classification, #d_stage, #d_type, #d_problem, #d_cause, #d_curative, #d_preventive, #d_status",
            ).prop("disabled", true);

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
                document.getElementById("problemDetailModal"),
            );
            modal.show();
        });

        $(document).on("click", ".btn-excel", function () {
            var id = $(this).data("id");
            if (!id) return;

            var btn = $(this);
            var originalText = btn.html();
            btn.prop("disabled", true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...',
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
                                "Content-Disposition",
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
                                    data.message || "Error exporting Excel",
                                );
                            })
                            .catch((err) => {
                                // If json parse fails, use status text
                                throw new Error(
                                    "Export failed: " + response.statusText,
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
                            "?",
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
                        if (currentStatus === "dispatched") {
                            $btn.text("Update to Closed").data(
                                "status",
                                "closed",
                            );
                        } else if (currentStatus === "closed") {
                            $btn.remove();
                        } else if (currentStatus === "in_progress") {
                            $btn.text("Update to Dispatched").data(
                                "status",
                                "dispatched",
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

        // Edit Feature Handlers
        $("#d_project").on("change", function () {
            var pid = $(this).val();
            $("#d_kanban")
                .empty()
                .append('<option value="">Select kanban</option>');
            if (!pid) return;
            ajax({
                url: "/kanbans/list",
                method: "GET",
                data: { project_id: pid },
            }).done(function (items) {
                items.forEach(function (k) {
                    $("#d_kanban").append(
                        `<option value="${k.id_kanban}">${k.kanban_name}</option>`,
                    );
                });
                // Update prefix if in edit mode
                if (!$("#d_group_code_edit_container").hasClass("d-none")) {
                    $("#d_gc_prefix").text(buildModalGroupCodePrefix());
                }
            });
        });

        $("#d_kanban, #d_type").on("change", function () {
            if (!$("#d_group_code_edit_container").hasClass("d-none")) {
                $("#d_gc_prefix").text(buildModalGroupCodePrefix());
            }
        });

        $("#btn-edit-problem").on("click", function () {
            $(this).addClass("d-none");
            $("#btn-save-problem").removeClass("d-none");
            $(
                "#d_project, #d_kanban, #d_item, #d_location, #d_machine, #d_type_saibo, #d_classification, #d_stage, #d_type, #d_problem, #d_cause, #d_curative, #d_preventive, #d_status",
            ).prop("disabled", false);

            var type = $("#d_type").val();
            if (type === 'manufacturing') return; // Skip group code logic for manufacturing

            var existingCode = $("#d_group_code").val();
            if (!existingCode) {
                // If code is empty, switch to edit mode
                $("#d_group_code_container").addClass("d-none");
                $("#d_group_code_edit_container").removeClass("d-none");
                var prefix = buildModalGroupCodePrefix();
                $("#d_gc_prefix").text(prefix);
                $("#d_gc_suffix").focus();
            } else {
                // If code exists, keep it visible but maybe disabled (read-only)
                // Assuming we don't edit existing codes
            }
        });

        $("#btn-save-problem").on("click", function () {
            var id = $(this).data("id");
            var data = {
                id_project: $("#d_project").val(),
                id_kanban: $("#d_kanban").val(),
                id_item: $("#d_item").val(),
                id_location: $("#d_location").val(),
                id_machine: $("#d_machine").val(),
                type_saibo: $("#d_type_saibo").val(),
                classification: $("#d_classification").val(),
                stage: $("#d_stage").val(),
                id_seksi_in_charge: $("#d_seksi_in_charge").val(),
                id_pic: $("#d_pic").val(),
                hour: $("#d_hour").val(),
                type: $("#d_type").val(),
                status: $("#d_status").val(),
                problem: $("#d_problem").val(),
                cause: $("#d_cause").val(),
                curative: $("#d_curative").val(),
                preventive: $("#d_preventive").val(),
            };

            // Check if we are updating group code
            if (!$("#d_group_code_edit_container").hasClass("d-none")) {
                var prefix = $("#d_gc_prefix").text();
                var suffix = $("#d_gc_suffix").val().trim();
                if (suffix) {
                    data.group_code = prefix + suffix;
                    data.group_code_suffix = suffix; // Optional, maybe for backend logic
                }
            }

            // Basic validation
            if (!data.problem || !data.cause || !data.curative) {
                if (window.Swal)
                    Swal.fire(
                        "Error",
                        "Please fill all required fields",
                        "error",
                    );
                else alert("Please fill all required fields");
                return;
            }

            var $btn = $(this);
            $btn.prop("disabled", true);

            ajax({
                url: "/problems/" + id,
                method: "PUT",
                data: data,
            })
                .done(function () {
                    if (window.Swal)
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "Problem updated",
                            timer: 1500,
                            showConfirmButton: false,
                        });

                    // Disable fields and reset buttons
                    $(
                        "#d_project, #d_kanban, #d_item, #d_location, #d_machine, #d_type_saibo, #d_classification, #d_stage, #d_seksi_in_charge, #d_pic, #d_hour, #d_type, #d_problem, #d_cause, #d_curative, #d_preventive, #d_status",
                    ).prop("disabled", true);
                    $("#d_group_code").val(
                        data.group_code || $("#d_group_code").val(),
                    ); // Update if changed
                    $("#d_group_code_container").removeClass("d-none");
                    $("#d_group_code_edit_container").addClass("d-none");

                    $("#btn-save-problem").addClass("d-none");
                    $("#btn-edit-problem").removeClass("d-none");

                    if (table) table.ajax.reload(null, false);
                })
                .always(function () {
                    $btn.prop("disabled", false);
                });
        });

        window.loadProblems = initTable;
    });
})();
