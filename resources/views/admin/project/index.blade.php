<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Projects'])
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
                    'subTitle' => 'Project List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Projects</h5>
                        <button id="btnAdd" class="btn btn-primary">Add Project</button>
                    </div>
                    <div class="card-body">
                        <div id="tableContainer">

                        </div>
                    </div>
                </div>

                @include('admin.project.modaladd')
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/projects.js') }}"></script>
    <script>
        $(function() {
            $('#tableContainer').load('/admin/projects/table', function() {
                if (window.loadProjects) window.loadProjects();
            });
        });
    </script>
</body>

</html>
