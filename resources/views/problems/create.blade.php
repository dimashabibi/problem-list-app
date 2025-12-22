<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Add Problem'])
    @include('layouts.head-css')
    <style>
        .form-card {
            max-width: 960px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', ['title' => 'Problems', 'sub-title' => 'Add Problem'])
                <div class="row">
                    <div class="col-12">
                        <div class="card form-card">
                            <div class="card-header">
                                <h5 class="mb-0">Add Problem</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Form implementation will follow. This stub is to validate routing
                                    and layout.</p>
                                <a href="{{ url('/') }}" class="btn btn-secondary">Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
</body>

</html>
