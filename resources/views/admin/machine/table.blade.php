<table id="table-machine" class="table table-striped table-bordered dt-responsive nowrap"
    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
    <thead>
        <tr>
            @can('admin')
                <th class="text-center" style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
            @endcan
            <th style="width: 5%;">No</th>
            <th>Machine Name</th>
            <th>Description</th>
            @can('admin')
                <th style="width: 15%;">Action</th>
            @endcan
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
