<div class="main-nav">
    <div class="d-flex justify-content-between main-logo-box">
        <!-- Sidebar Logo -->
        <div class="logo-box">
            <a href="{{ url('/admin/dashboard') }}" class="logo-dark">
                <img src="{{ asset('assets/images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
                <img src="{{ asset('assets/images/logo-dark.png') }}" class="logo-lg" alt="logo dark">
            </a>

            <a href="{{ url('/admin/dashboard') }}" class="logo-light">
                <img src="{{ asset('assets/images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
                <img src="{{ asset('assets/images/logo-white.png') }}" class="logo-lg" alt="logo light">
            </a>
        </div>
    </div>

    <div class="h-100" data-simplebar>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-item">
                <a class="menu-link" href="{{ url('/admin/dashboard') }}">
                    <span class="nav-icon">
                        <i data-lucide="house"></i>
                    </span>
                    <span class="nav-text"> Dashboard </span>
                </a>
            </li>

            <li class="menu-title">Problem Management</li>

            <li class="menu-item">
                <a class="menu-link" href="{{ url('/admin/problems') }}">
                    <span class="nav-icon">
                        <i data-lucide="triangle-alert"></i>
                    </span>
                    <span class="nav-text"> Problem List </span>
                </a>
            </li>

            <li class="menu-title">Project Management</li>

            <li class="menu-item">
                <a class="menu-link" href="{{ url('/admin/projects') }}">
                    <span class="nav-icon">
                        <i data-lucide="origami"></i>
                    </span>
                    <span class="nav-text"> Project List </span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link" href="{{ url('/admin/items') }}">
                    <span class="nav-icon">
                        <i data-lucide="package"></i>
                    </span>
                    <span class="nav-text"> Item List </span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link" href="/admin/kanbans">
                    <span class="nav-icon">
                        <i data-lucide="clipboard-check"></i>
                    </span>
                    <span class="nav-text"> Kanban List </span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link" href="{{ url('/admin/locations') }}">
                    <span class="nav-icon">
                        <i data-lucide="map-pinned"></i>
                    </span>
                    <span class="nav-text"> Location List </span>
                </a>
            </li>

            <li class="menu-title">User Management</li>

            <li class="menu-item">
                <a class="menu-link" href="{{ url('/admin/users') }}">
                    <span class="nav-icon">
                        <i data-lucide="square-user-round"></i>
                    </span>
                    <span class="nav-text"> User List </span>
                </a>
            </li>

        </ul>
    </div>
</div>
