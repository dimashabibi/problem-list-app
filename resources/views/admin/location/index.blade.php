<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Locations'])
    @include('layouts.head-css')
</head>

<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', [
                    'title' => 'Project Management',
                    'subTitle' => 'Location List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Locations</h5>
                        <div>
                            @can('admin')
                                <button id="btnBulkDelete" class="btn btn-danger d-none me-2">Delete Selected</button>
                            @endcan
                            @can('admin')
                                <button id="btnLocationAdd" class="btn btn-primary">Add Location</button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="locationsTableContainer"></div>
                    </div>
                </div>

                @can('admin')
                    @include('admin.location.modaladd')
                @endcan
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/locations.js') }}"></script>
    <script>
        $(function() {
            $('#locationsTableContainer').load('/locations/table', function() {
                if (window.loadLocations) window.loadLocations();
            });
        });
    </script>
</body>

</html>
