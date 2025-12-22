<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.title-meta', ['subTitle' => 'Users'])
    @include('layouts.head-css')
</head>
<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', ['title' => 'Admin', 'subTitle' => 'User List'])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Users</h5>
                        <button id="btnUserAdd" class="btn btn-primary">Add User</button>
                    </div>
                    <div class="card-body">
                        <div id="usersTableContainer"></div>
                    </div>
                </div>

                @include('admin.user.modaladd')
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/js/pages/users.js') }}"></script>
    <script>
        $(function(){
            $('#usersTableContainer').load('/admin/users/table', function(){
                if (window.loadUsers) window.loadUsers();
            });
        });
    </script>
</body>
</html>
