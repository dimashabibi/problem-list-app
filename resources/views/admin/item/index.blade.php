<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Items'])
    @include('layouts.head-css')
</head>

<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', [
                    'title' => 'Item Management',
                    'subTitle' => 'Item List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Items</h5>
                        <div>
                            <button id="btnBulkDelete" class="btn btn-danger d-none me-2">Delete Selected</button>
                            <button id="btnAdd" class="btn btn-primary">Add Item</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="tableContainer">

                        </div>
                    </div>
                </div>

                @include('admin.item.modaladd')
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/items.js') }}"></script>
    <script>
        $(function() {
            $('#tableContainer').load('/admin/items/table', function() {
                if (window.loadItems) window.loadItems();
            });
        });
    </script>
</body>

</html>
