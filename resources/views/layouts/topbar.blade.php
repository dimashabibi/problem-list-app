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

                        <a class="dropdown-item" href="pages-profile.html">
                            <i data-lucide="circle-user" class="fs-16 text-muted align-middle me-2"></i><span
                                class="align-middle">My Account</span>
                        </a>

                        <a class="dropdown-item" href="pages-pricing.html">
                            <i data-lucide="badge-percent" class="fs-16 text-muted align-middle me-2"></i><span
                                class="align-middle">Pricing</span>
                        </a>
                        <a class="dropdown-item" href="pages-faqs.html">
                            <i data-lucide="circle-help" class="fs-16 text-muted align-middle me-2"></i><span
                                class="align-middle">Help</span>
                        </a>
                        <a class="dropdown-item" href="pages-gallery.html">
                            <i data-lucide="book-image" class="fs-16 text-muted align-middle me-2"></i>
                            <span class="align-middle">Photos</span>
                            <span class="align-middle float-end badge badge-soft-danger">New</span>
                        </a>

                        <div class="dropdown-divider my-1"></div>

                        <a class="dropdown-item" href="auth-lock-screen.html">
                            <i data-lucide="lock" class="fs-16 text-muted align-middle me-2"></i><span
                                class="align-middle">Lock screen</span>
                        </a>
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
