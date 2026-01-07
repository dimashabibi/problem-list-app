<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Dashboard | T-QIMS'])
    @include('layouts.head-css')
</head>

<body>

    <!-- START Wrapper -->
    <div class="wrapper">

        @include('layouts.main-nav')

        @include('layouts.topbar')

        <!-- Start Content here -->
        <div class="page-container">

            <!-- Start Container Fluid -->
            <div class="page-content">

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="page-title-box">
                                <h4 class="mb-0 fw-semibold">Dashboard</h4>
                            </div>
                            <form action="{{ route('admin.dashboard') }}" method="GET"
                                class="d-flex align-items-center gap-2">
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="{{ $startDate ?? '' }}" placeholder="Start Date">
                                <span class="text-muted">-</span>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="{{ $endDate ?? '' }}" placeholder="End Date">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i data-lucide="filter" class="w-4 h-4"></i>
                                </button>
                                @if (request('start_date') || request('end_date'))
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary">
                                        Clear
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-2 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-3 card-title">Total Manufacturing</p>
                                        <h2 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">
                                            {{ $dataTotal['totalMfgProblems'] }}</h2>
                                    </div>
                                    <div>
                                        <i data-lucide="wallet" class="fs-32 text-primary"></i>
                                    </div>
                                </div>
                                <div class="row align-items-center mt-4">
                                    <div class="col-12">
                                        <div id="sales_funnel" class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-3 card-title">Total Kentokai</p>
                                        <h2 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">
                                            {{ $dataTotal['totalKentokaiProblems'] }}</h2>
                                    </div>
                                    <div>
                                        <i data-lucide="search" class="fs-32 text-primary"></i>
                                    </div>
                                </div>
                                <div class="row align-items-center mt-4">
                                    <div class="col-12">
                                        <div id="kentokai_funnel" class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-3 card-title">Total KS Problem</p>
                                        <h2 class="fw-bold d-flex align-items-center gap-2 mb-0">
                                            {{ $dataTotal['totalKsProblems'] }}</h2>
                                    </div>
                                    <div>
                                        <i data-lucide="briefcase-conveyor-belt" class="fs-32 text-primary"></i>
                                    </div>
                                </div>
                                <div class="row align-items-center mt-4">
                                    <div class="col-12">
                                        <div id="order_funnel" class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-3 card-title">Total KD Problem</p>
                                        <h2 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">
                                            {{ $dataTotal['totalKdProblems'] }}</h2>
                                    </div>
                                    <div>
                                        <i data-lucide="shield-minus" class="fs-32 text-primary"></i>
                                    </div>
                                </div>
                                <div class="row align-items-center mt-4">
                                    <div class="col-12">
                                        <div id="cancel_funnel" class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-3 card-title">Total SK Problem</p>
                                        <h2 class="fw-bold d-flex align-items-center gap-2 mb-0">
                                            {{ $dataTotal['totalSkProblems'] }}</h2>
                                    </div>
                                    <div>
                                        <i data-lucide="users" class="fs-32 text-primary"></i>
                                    </div>
                                </div>
                                <div class="row align-items-center mt-4">
                                    <div class="col-12">
                                        <div id="customer_funnel" class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-3 card-title">Total Buyoff</p>
                                        <h2 class="fw-bold text-primary d-flex align-items-center gap-2 mb-0">
                                            {{ $dataTotal['totalBuyoffProblems'] }}</h2>
                                    </div>
                                    <div>
                                        <i data-lucide="check-circle" class="fs-32 text-primary"></i>
                                    </div>
                                </div>
                                <div class="row align-items-center mt-4">
                                    <div class="col-12">
                                        <div id="buyoff_funnel" class="apex-charts"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Unresolved SK Problems.</h4>
                                </div>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle btn btn-sm btn-link text-uppercase fw-semibold px-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Weekly
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Week</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Months</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Years</a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="text-center">
                                    <p class="text-muted mb-0">You have <span
                                            class="text-success fw-bold">{{ $thisWeekProblems['sk'] }}</span>
                                        unresolved issues.</p>
                                </div>
                                <div id="columnChartSk" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Unresolved KS Problems.</h4>
                                </div>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle btn btn-sm btn-link text-uppercase fw-semibold px-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Weekly
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Week</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Months</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Years</a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="text-center">
                                    <p class="text-muted mb-0">You have <span
                                            class="text-success fw-bold">{{ $thisWeekProblems['ks'] }}</span>
                                        unresolved issues.</p>
                                </div>
                                <div id="columnChartKs" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Unresolved KD Problems.</h4>
                                </div>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle btn btn-sm btn-link text-uppercase fw-semibold px-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Weekly
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Week</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Months</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Years</a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="text-center">
                                    <p class="text-muted mb-0">You have <span
                                            class="text-success fw-bold">{{ $thisWeekProblems['kd'] }}</span>
                                        unresolved issues.</p>
                                </div>
                                <div id="columnChartKd" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-xl-4 col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Manufacturing Problem Collection</h4>
                                </div>
                                <div>
                                    <div class="dropdown">
                                        <a href="#"
                                            class="dropdown-toggle btn btn-sm btn-link text-uppercase fw-semibold px-0"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            Weekly
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- item-->
                                            <a href="#!" class="dropdown-item">Week</a>
                                            <!-- item-->
                                            <a href="#!" class="dropdown-item">Months</a>
                                            <!-- item-->
                                            <a href="#!" class="dropdown-item">Years</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <p class="text-muted mb-4">You have <span
                                            class="text-success fw-bold">{{ $thisWeekProblems['pie'] }}</span>
                                        issues in the manufacturing process this week.</p>
                                </div>

                                <div id="pieChart" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Unresolved Manufacturing Problems.</h4>
                                </div>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle btn btn-sm btn-link text-uppercase fw-semibold px-0"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Weekly
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Week</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Months</a>
                                        <!-- item-->
                                        <a href="#!" class="dropdown-item">Years</a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="text-center">
                                    <p class="text-muted mb-0">You have <span
                                            class="text-success fw-bold">{{ $thisWeekProblems['column1'] }}</span>
                                        unresolved issues.</p>
                                </div>
                                <div id="columnChart1" class="apex-charts"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-lg-6">

                    </div>

                    <div class="col-xl-4 col-lg-6">

                    </div>

                    <div class="col-xl-5 col-lg-12">

                    </div>
                </div>
            </div>
            <!-- End Container Fluid -->

            @include('layouts.footer')

            <div id="sales_funnel" class="d-none"></div>

        </div>
        <!-- End Page Content -->

    </div>
    <!-- END Wrapper -->

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script>
        var pieData = @json($pieData);
        var columnChartData = @json($columnChart1);
        var columnChartSkData = @json($columnChartSk);
        var columnChartKdData = @json($columnChartKd);
        var columnChartKsData = @json($columnChartKs);
    </script>
    <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
</body>

</html>
