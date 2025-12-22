<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Problems'])
    @include('layouts.head-css')
</head>

<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', [
                    'title' => 'Problem Management',
                    'subTitle' => 'Problem List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Problems</h5>
                        <button id="btnProblemAdd" class="btn btn-primary">Add Problem</button>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#manufacturing" role="tab"
                                    data-type="manufacturing">
                                    <span class="d-none d-sm-block">Manufacturing</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ks" role="tab" data-type="ks">
                                    <span class="d-none d-sm-block">KS</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kd" role="tab" data-type="kd">
                                    <span class="d-none d-sm-block">KD</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#sk" role="tab" data-type="sk">
                                    <span class="d-none d-sm-block">SK</span>
                                </a>
                            </li>
                        </ul>
                        <div id="problemsTableContainer"></div>
                    </div>
                </div>

                @include('admin.problem.modaladd')
                @include('admin.problem.modaldetail')
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/problems.js') }}"></script>
    <script src="{{ asset('assets/js/components/form-clipboard.js') }}"></script>
    <script>
        $(function() {
            $('#problemsTableContainer').load('/admin/problems/table', function() {
                if (window.loadProblems) window.loadProblems();
            });
        });
    </script>
</body>

</html>
