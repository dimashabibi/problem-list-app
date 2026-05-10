<table id="table-kanban" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
    <thead>
        <tr>
            @can('admin')
                <th class="text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
            @endcan
            <th>No</th>
            <th>Project</th>
            <th>Kanban Name</th>
            <th>Part Name</th>
            <th>Part Number</th>
            @can('admin')
                <th>Actions</th>
            @endcan
        </tr>
    </thead>
    <tbody></tbody>
</table>
