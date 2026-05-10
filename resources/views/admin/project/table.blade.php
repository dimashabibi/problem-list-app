<table id="table-project" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
    <thead>
        <tr>
            @can('admin')
                <th class="text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
            @endcan
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            @can('admin')
                <th>Actions</th>
            @endcan
        </tr>
    </thead>
    <tbody></tbody>
</table>
