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
                                        <select id="d_location" class="form-control" disabled data-choices data-choices-search-true>
                                            <option value="">Select location</option>
                                            @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                                <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_machine" class="form-label">Machine</label>
                                        <select id="d_machine" class="form-control" disabled data-choices data-choices-search-true>
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
                                        <select id="d_type_saibo" class="form-control" disabled data-choices data-choices-search-true>
                                            <option value="">Select type</option>
                                            <option value="baru">Baru</option>
                                            <option value="berulang">Berulang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_classification" class="form-label">Classification</label>
                                        <select id="d_classification" class="form-control" disabled data-choices data-choices-search-true>
                                            <option value="">Select classification</option>
                                            <option value="konst">Konst</option>
                                            <option value="komp">Komp</option>
                                            <option value="model">Model</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_classification_problem" class="form-label">Classification
                                            Problem</label>
                                        <select id="d_classification_problem" class="form-control" disabled data-choices data-choices-search-true>
                                            <option value="">Select classification problem</option>
                                            <option value="DD  -Construction Making">DD -Construction Making</option>
                                            <option value="DD - Stock List">DD - Stock List</option>
                                            <option value="DD - Shutter making">DD - Shutter making</option>
                                            <option value="DD - Construction Interference">DD - Construction
                                                Interference</option>
                                            <option value="DD - Global STD implementation">DD - Global STD
                                                implementation</option>
                                            <option value="DD - Component library">DD - Component library</option>
                                            <option value="DD - Dataout">DD - Dataout</option>
                                            <option value="DD - Machining Attribute">DD - Machining Attribute</option>
                                            <option value="DD - CAD Data Using">DD - CAD Data Using</option>
                                            <option value="PM - Component installation">PM - Component installation
                                            </option>
                                            <option value="PM - Material assy">PM - Material assy</option>
                                            <option value="PM - Handwork">PM - Handwork</option>
                                            <option value="PM - Identity marking">PM - Identity marking</option>
                                            <option value="PM - NC slice">PM - NC slice</option>
                                            <option value="CC - DIE - ncdata process area">CC - DIE - ncdata process
                                                area</option>
                                            <option value="CC - DIE - G-Code ">CC - DIE - G-Code </option>
                                            <option value="CC - DIE - ncdata allowance">CC - DIE - ncdata allowance
                                            </option>
                                            <option value="CC - DIE - ncdata interference">CC - DIE - ncdata
                                                interference</option>
                                            <option value="CC - DIE - ncdata amount">CC - DIE - ncdata amount</option>
                                            <option value="CC - DIE - Overtravel data">CC - DIE - Overtravel data
                                            </option>
                                            <option value="CC - BN - ncdata process area">CC - BN - ncdata process area
                                            </option>
                                            <option value="CC - BN - G-Code ">CC - BN - G-Code </option>
                                            <option value="CC - BN - ncdata allowance">CC - BN - ncdata allowance
                                            </option>
                                            <option value="CC - BN - ncdata interference">CC - BN - ncdata interference
                                            </option>
                                            <option value="CC - BN - ncdata amount">CC - BN - ncdata amount</option>
                                            <option value="CC - BN - Overtravel data">CC - BN - Overtravel data
                                            </option>
                                            <option value="CC - KN - ncdata process area">CC - KN - ncdata process area
                                            </option>
                                            <option value="CC - KN - G-Code ">CC - KN - G-Code </option>
                                            <option value="CC - KN - ncdata allowance">CC - KN - ncdata allowance
                                            </option>
                                            <option value="CC - KN - ncdata interference">CC - KN - ncdata interference
                                            </option>
                                            <option value="CC - KN - ncdata amount">CC - KN - ncdata amount</option>
                                            <option value="CC - KN - Overtravel data">CC - KN - Overtravel data
                                            </option>
                                            <option value="Mch - manual process">Mch - manual process</option>
                                            <option value="Mch - Datum setting">Mch - Datum setting</option>
                                            <option value="Mch - Machine Performance">Mch - Machine Performance
                                            </option>
                                            <option value="Mch - Dandori">Mch - Dandori</option>
                                            <option value="Mch - ncdata offset">Mch - ncdata offset</option>
                                            <option value="Mch - Ncdata transfer">Mch - Ncdata transfer</option>
                                            <option value="Mch - Dimension check">Mch - Dimension check</option>
                                            <option value="Mch - Tool using">Mch - Tool using</option>
                                            <option value="DBCCA - Component order">DBCCA - Component order</option>
                                            <option value="DBCCA - Component arrival time">DBCCA - Component arrival
                                                time</option>
                                            <option value="DBCCA - Component amount">DBCCA - Component amount</option>
                                            <option value="DF - Surface model">DF - Surface model</option>
                                            <option value="DF - Profile">DF - Profile</option>
                                            <option value="QOH-Equipment">QOH-Equipment</option>
                                            <option value="QOH-X File">QOH-X File</option>
                                            <option value="QOH-Standard">QOH-Standard</option>
                                            <option value="QOH-Process">QOH-Process</option>
                                            <option value="QOH-Misc Judgement">QOH-Misc Judgement</option>
                                            <option value="Casting - blow hole">Casting - blow hole</option>
                                            <option value="casting - material minus">casting - material minus</option>
                                            <option value="DMCA-Distribution">DMCA-Distribution</option>
                                            <option value="DMCA-Scheduling">DMCA-Scheduling</option>
                                            <option value="DEng-Equipment">DEng-Equipment</option>
                                            <option value="DEng-X File">DEng-X File</option>
                                            <option value="DEng-Standard">DEng-Standard</option>
                                            <option value="DEng-Process">DEng-Process</option>
                                            <option value="DEng-Misc Judgement">DEng-Misc Judgement</option>
                                            <option value="DAT - Assy Insert">DAT - Assy Insert</option>
                                            <option value="DAT - Assy komponen">DAT - Assy komponen</option>
                                            <option value="DAT - Welding  blow hole">DAT - Welding blow hole</option>
                                            <option value="DAT - Assy shutter">DAT - Assy shutter</option>
                                            <option value="DAT - Interference komponen">DAT - Interference komponen
                                            </option>
                                            <option value="DAT - Polishing">DAT - Polishing</option>
                                            <option value="DAT - Flame hard (HRC)">DAT - Flame hard (HRC)</option>
                                            <option value="DAT - Tightening bolt">DAT - Tightening bolt</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="d_seksi_in_charge" class="form-label">Seksi In Charge</label>
                                        <select id="d_seksi_in_charge" class="form-control" disabled data-choices data-choices-search-true>
                                            <option value="">Select seksi</option>
                                            @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                                <option value="{{ $l->id_location }}">{{ $l->location_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="d_stage" class="form-label">Stage</label>
                                        <select id="d_stage" class="form-control" disabled data-choices data-choices-search-true>
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
                                    <div class="col-md-3">
                                        <label for="d_created_at" class="form-label">Created At</label>
                                        <input type="text" id="d_created_at" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="d_dispatched_at" class="form-label">Dispatched At</label>
                                        <input type="text" id="d_dispatched_at" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="d_closed_at" class="form-label">Closed At</label>
                                        <input type="text" id="d_closed_at" class="form-control" disabled>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="d_target" class="form-label">Target</label>
                                        <input type="text" id="d_target" class="form-control" disabled>
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
                                        <label class="form-label">Curative</label>
                                        <div id="d_curative_container"></div>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 d-none"
                                            id="d_add_curative_btn">
                                            <i data-lucide="plus-lg"></i> Add Curative
                                        </button>
                                        <textarea id="d_curative" class="form-control d-none" rows="3" disabled></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Preventive</label>
                                        <div id="d_preventive_container"></div>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 d-none"
                                            id="d_add_preventive_btn">
                                            <i data-lucide="plus-lg"></i> Add Preventive
                                        </button>
                                        <textarea id="d_preventive" class="form-control d-none" rows="3" disabled></textarea>
                                    </div>
                                </div>
                            </form>

                            <!-- Hidden Templates for Detail Modal -->
                            <div class="d-none">
                                <div id="d_curative_template">
                                    <div class="input-group mb-2 d-curative-row">
                                        <input type="text" class="form-control" name="curative_actions[]"
                                            placeholder="Curative Action" required>
                                        <input type="number" step="0.01" class="form-control"
                                            name="curative_hours[]" placeholder="Hour" style="max-width: 100px;">
                                        <select class="form-select" name="curative_pics[]" style="max-width: 200px;">
                                            <option value="">Select PIC</option>
                                            @foreach (\App\Models\Location::orderBy('location_name')->get() as $l)
                                                <option value="{{ $l->id_location }}">{{ $l->location_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-danger d-remove-row-btn">
                                            <i data-lucide="trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="d_preventive_template">
                                    <div class="input-group mb-2 d-preventive-row">
                                        <input type="text" class="form-control" name="preventive_actions[]"
                                            placeholder="Preventive Action" required>
                                        <button type="button" class="btn btn-outline-danger d-remove-row-btn">
                                            <i data-lucide="trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
