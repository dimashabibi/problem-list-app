<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Dashboard'])
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


            </div>
            <!-- End Container Fluid -->

            @include('layouts.footer')

            <div id="sales_funnel" class="d-none"></div>

        </div>
        <!-- End Page Content -->

    </div>
    <!-- END Wrapper -->

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/dashboard.js') }}?v={{ time() + 1 }}"></script>

</body>

</html>
