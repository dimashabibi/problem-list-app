<div class="modal fade" id="problemDetailModal" tabindex="-1" aria-labelledby="problemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="problemDetailModalLabel">Problem Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card border-0">
                    <div class="card-body">
                        <!-- Attachments Section (Carousel at the top) -->
                        <div class="mb-4">
                            <h6 class="card-title text-muted mb-3">Attachments</h6>
                            <div id="attachment-carousel"></div>
                        </div>

                        <!-- Details Section -->
                        <div class="container py-3">
                            <form>
                                <div class="row g-3">
                                    <!-- Row 1 -->
                                    <div class="col-md-6">
                                        <label for="d_project" class="form-label">Project</label>
                                        <select id="d_project" class="form-control" disabled>
                                            <option value="">Select project</option>
                                            @foreach (\App\Models\Project::orderBy('project_name')->get() as $p)
                                                <option value="{{ $p->id_project }}">{{ $p->project_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_kanban" class="form-label">Kanban</label>
                                        <select id="d_kanban" class="form-control" disabled>
                                            <option value="">Select kanban</option>
                                        </select>
                                    </div>

                                    <!-- Row 2 -->
                                    <div class="col-md-6">
                                        <label for="d_item" class="form-label">Item</label>
                                        <select id="d_item" class="form-control" disabled>
                                            <option value="">Select item</option>
                                            @foreach (\App\Models\Item::orderBy('item_name')->get() as $i)
                                                <option value="{{ $i->id_item }}">{{ $i->item_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_location" class="form-label">Location</label>
                                        <select id="d_location" class="form-control" disabled>
                                            <option value="">Select location</option>
                                            @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                                <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_machine" class="form-label">Machine</label>
                                        <select id="d_machine" class="form-control" disabled>
                                            <option value="">Select machine</option>
                                            @foreach (\App\Models\Machine::orderBy('name_machine')->get() as $m)
                                                <option value="{{ $m->id_machine }}">{{ $m->name_machine }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Row 3 -->
                                    <div class="col-md-6">
                                        <label for="d_group_code" class="form-label">Group Code</label>
                                        <div id="d_group_code_container">
                                            <input type="text" id="d_group_code" class="form-control" disabled>
                                        </div>
                                        <div id="d_group_code_edit_container" class="input-group d-none">
                                            <span class="input-group-text" id="d_gc_prefix"></span>
                                            <input type="text" id="d_gc_suffix" class="form-control"
                                                placeholder="Suffix">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_type" class="form-label">Type</label>
                                        <select id="d_type" class="form-control" disabled>
                                            <option value="manufacturing">Manufacturing</option>
                                            <option value="kentokai">Kentokai</option>
                                            <option value="ks">KS</option>
                                            <option value="kd">KD</option>
                                            <option value="sk">SK</option>
                                            <option value="buyoff">Buyoff</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_status" class="form-label">Status</label>
                                        <select id="d_status" class="form-control" disabled>
                                            <option value="in_progress">In Progress</option>
                                            <option value="dispatched">Dispatched</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_type_saibo" class="form-label">Type Saibo</label>
                                        <select id="d_type_saibo" class="form-control" disabled>
                                            <option value="">Select type</option>
                                            <option value="baru">Baru</option>
                                            <option value="berulang">Berulang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_classification" class="form-label">Classification</label>
                                        <select id="d_classification" class="form-control" disabled>
                                            <option value="">Select classification</option>
                                            <option value="konst">Konst</option>
                                            <option value="komp">Komp</option>
                                            <option value="model">Model</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_seksi_in_charge" class="form-label">Seksi In Charge</label>
                                        <select id="d_seksi_in_charge" class="form-control" disabled>
                                            <option value="">Select seksi</option>
                                            @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                                <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_pic" class="form-label">PIC</label>
                                        <select id="d_pic" class="form-control" disabled>
                                            <option value="">Select PIC</option>
                                            @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                                <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_hour" class="form-label">Hour</label>
                                        <input type="number" id="d_hour" class="form-control" min="0" max="255" placeholder="Hour" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_stage" class="form-label">Stage</label>
                                        <select id="d_stage" class="form-control" disabled>
                                            <option value="">Select stage</option>
                                            @foreach (['MFG', 'KS', 'KD', 'SK', 'T0', 'T1', 'T2', 'T3', 'BUYOFF', 'LT', 'HOMELINE'] as $st)
                                                <option value="{{ $st }}">{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Row 4 -->
                                    <div class="col-md-6">
                                        <label for="d_reporter" class="form-label">Reporter</label>
                                        <input type="text" id="d_reporter" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_created_at" class="form-label">Created At</label>
                                        <input type="text" id="d_created_at" class="form-control" disabled>
                                    </div>

                                    <!-- Row 5 (Textarea) -->
                                    <div class="col-12">
                                        <label for="d_problem" class="form-label">Problem</label>
                                        <textarea id="d_problem" class="form-control" rows="3" disabled></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="d_cause" class="form-label">Cause</label>
                                        <textarea id="d_cause" class="form-control" rows="3" disabled></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="d_curative" class="form-label">Curative</label>
                                        <textarea id="d_curative" class="form-control" rows="3" disabled></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label for="d_preventive" class="form-label">Preventive</label>
                                        <textarea id="d_preventive" class="form-control" rows="3" disabled></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-edit-problem">Edit</button>
                <button type="button" class="btn btn-success d-none" id="btn-save-problem">Save</button>
            </div>
        </div>
    </div>
</div>
