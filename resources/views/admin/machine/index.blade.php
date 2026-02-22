<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Machine List | T-QIMS'])
    @include('layouts.head-css')
</head>

<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', [
                    'title' => 'Machine Management',
                    'subTitle' => 'Machine List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Machines</h5>
                        <div>
                            <button id="btnBulkDelete" class="btn btn-danger d-none me-2">Delete Selected</button>
                            <button id="btnAdd" class="btn btn-primary">Add Machine</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="tableContainer">
                            @include('admin.machine.table')
                        </div>
                    </div>
                </div>

                @include('admin.machine.modaladd')
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/machines.js') }}"></script>
    <script>
        $(function() {
            if (window.loadMachines) window.loadMachines();
        });
    </script>
</body>

</html>
