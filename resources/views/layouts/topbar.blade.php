<header class="topbar d-flex">
    <div class="container-fluid">
        <div class="navbar-header">

            <div class="d-flex align-items-center gap-2">
                <!-- Menu Toggle Button -->
                <div class="topbar-item">
                    <button type="button" class="topbar-button fs-24 button-toggle-menu">
                        <i data-lucide="menu"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <!-- Theme Color (Light/Dark) -->
                <div class="topbar-item">
                    <button type="button" class="topbar-button fs-24" id="light-dark-mode">
                        <i data-lucide="moon" class="light-mode"></i>
                        <i data-lucide="sun" class="dark-mode"></i>
                    </button>
                </div>

                <!-- User -->
                <div class="dropdown topbar-item">
                    <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center gap-2">
                            <span class="d-lg-flex flex-column gap-1 d-none">
                                <h5 class="my-0 text-reset fs-14">
                                    {{ auth()->user()->fullname ?? (auth()->user()->username ?? 'User') }}</h5>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <form action="{{ route('logout') }}" method="POST" class="dropdown-item p-0">
                            @csrf
                            <button type="submit" class="btn w-100 text-start">
                                <i data-lucide="log-out" class="fs-16 text-muted align-middle me-2"></i>
                                <span class="align-middle">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
