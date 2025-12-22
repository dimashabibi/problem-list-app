<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Kanban'])
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
                    'subTitle' => 'Kanban List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kanbans</h5>
                        <div class="d-flex align-items-center gap-2">
                            <button id="btnKanbanAdd" class="btn btn-primary">Add Kanban</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="kanbanTableContainer"></div>
                    </div>
                </div>

                @include('admin.kanban.modaladd', ['projects' => $projects])
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/kanbans.js') }}"></script>
    <script>
        $(function() {
            $('#kanbanTableContainer').load('/admin/kanbans/table', function() {
                if (window.loadKanbans) window.loadKanbans();
            });
        });
    </script>
</body>

</html>
