<div class="modal fade" id="problemModal" tabindex="-1" aria-labelledby="problemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="problemModalLabel">Add Problem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="problemForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <div class="input-group">
                                    <select class="form-control" id="p_project">
                                        <option value="">Select project</option>
                                        @foreach (\App\Models\Project::orderBy('project_name')->get() as $p)
                                            <option value="{{ $p->id_project }}">{{ $p->project_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control d-none" id="new_project_name"
                                        placeholder="New Project Name">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleProjectMode"
                                        title="Create New Project">
                                        <i class="bi bi-plus-lg"></i> New
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kanban</label>
                                <div class="input-group">
                                    <select class="form-control" id="p_kanban">
                                        <option value="">Select kanban</option>
                                    </select>
                                    <input type="text" class="form-control d-none" id="new_kanban_name"
                                        placeholder="New Kanban Name">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleKanbanMode"
                                        title="Create New Kanban">
                                        <i class="bi bi-plus-lg"></i> New
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Item</label>
                                <div class="input-group">
                                    <select class="form-control" id="p_item_select">
                                        <option value="">Select item</option>
                                        @foreach (\App\Models\Item::orderBy('item_name')->get() as $i)
                                            <option value="{{ $i->id_item }}">{{ $i->item_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control d-none" id="new_item_name"
                                        placeholder="New Item Name">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleItemMode"
                                        title="Create New Item">
                                        <i class="bi bi-plus-lg"></i> New
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <select class="form-control" id="p_location">
                                    <option value="">Select location</option>
                                    @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                        <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Machine</label>
                                <select class="form-control" id="p_machine">
                                    <option value="">Select machine</option>
                                    @foreach (\App\Models\Machine::orderBy('name_machine')->get() as $m)
                                        <option value="{{ $m->id_machine }}">{{ $m->name_machine }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stage</label>
                                <select class="form-control" id="p_stage">
                                    <option value="">Select stage</option>
                                    @foreach (['MFG', 'KS', 'KD', 'SK', 'T0', 'T1', 'T2', 'T3', 'BUYOFF', 'LT', 'HOMELINE'] as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type Saibo</label>
                                <select class="form-control" id="p_type_saibo">
                                    <option value="">Select type</option>
                                    <option value="baru">Baru</option>
                                    <option value="berulang">Berulang</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Classification</label>
                                <select class="form-control" id="p_classification">
                                    <option value="">Select classification</option>
                                    <option value="konst">Konst</option>
                                    <option value="komp">Komp</option>
                                    <option value="model">Model</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Seksi In Charge</label>
                                <select class="form-control" id="p_seksi_in_charge">
                                    <option value="">Select seksi</option>
                                    @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                        <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">PIC</label>
                                <select class="form-control" id="p_pic">
                                    <option value="">Select PIC</option>
                                    @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                        <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hour</label>
                                <input type="number" class="form-control" id="p_hour" min="0"
                                    max="255" placeholder="Hour">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label d-block">Type</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="p_type"
                                        id="type_manufacturing" value="manufacturing" checked>
                                    <label class="form-check-label" for="type_manufacturing">Manufacturing</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="p_type" id="type_kentokai"
                                        value="kentokai">
                                    <label class="form-check-label" for="type_kentokai">Kentokai</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="p_type" id="type_ks"
                                        value="ks">
                                    <label class="form-check-label" for="type_ks">KS</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="p_type" id="type_kd"
                                        value="kd">
                                    <label class="form-check-label" for="type_kd">KD</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="p_type" id="type_sk"
                                        value="sk">
                                    <label class="form-check-label" for="type_sk">SK</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="p_type" id="type_buyoff"
                                        value="buyoff">
                                    <label class="form-check-label" for="type_buyoff">Buy Off</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label d-block">Problem Code</label>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <select id="group_code_select" name="group_code_select" class="form-select">
                                            <option value="">Select existing code</option>
                                            <option value="__new__">+ New Code</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="group_code_suffix" class="form-control d-none"
                                            placeholder="New code suffix">
                                    </div>
                                    <div class="col-md-3">
                                        <div id="group_code_preview" class="form-text text-muted"></div>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="group_code_clear">
                                            Clear
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="group_code_mode" name="group_code_mode">
                                <input type="hidden" id="group_code" name="group_code">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Problem</label>
                                <textarea class="form-control" id="p_problem" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Cause</label>
                                <textarea class="form-control" id="p_cause" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Curative</label>
                                <textarea class="form-control" id="p_curative" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Preventive</label>
                                <textarea class="form-control" id="p_preventive" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Attachments (images)</label>
                                <!-- Dropzone Area -->
                                <div class="dropzone" id="problem-dropzone"
                                    style="border: 2px dashed #ccc; background: #fafafa; min-height: 100px;">
                                    <div class="dz-message needsclick">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                                        </div>
                                        <h4>Drop files here or click to upload.</h4>
                                    </div>
                                </div>

                                <!-- Preview Container -->
                                <div id="dropzone-preview" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveProblem">Save</button>
            </div>
        </div>
    </div>
</div>
