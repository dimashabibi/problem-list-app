<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Login'])
    @include('layouts.head-css')
    <style>
        .login-card {
            max-width: 420px;
            margin: 8vh auto;
        }
    </style>
</head>

<body>
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-5">
                    <div class="card auth-card">
                        <div class="card-body">
                            <div class="p-3">
                                <div class="mx-auto mb-5 auth-logo text-center">
                                    <a href="index.html" class="logo-dark">
                                        <img src="assets/images/logo-dark.png" height="30" alt="logo dark">
                                    </a>

                                    <a href="index.html" class="logo-light">
                                        <img src="assets/images/logo-white.png" height="30" alt="logo light">
                                    </a>
                                </div>

                                <div class="text-center">
                                    <h3 class="fw-bold text-dark fs-20">Hi , Welcome Back 👋 </h3>
                                    <p class="text-muted mt-1 mb-4">Enter your credentials to access your
                                        account.</p>
                                </div>

                                <div class="p-3">
                                    <form method="POST" action="/login">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text"
                                                class="form-control @error('username') is-invalid @enderror"
                                                name="username" value="{{ old('username') }}" required
                                                autocomplete="username">
                                            @error('username')
                                                <div class="invalid-feedback">{{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                name="password" required autocomplete="current-password">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Login</button>
                                        </div>
                                    </form>

                                </div>

                                <a href="" class="text-center mt-4 mb-0">Forget Password?</a>
                            </div> <!-- end col -->
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->
            @include('layouts.footer')
        </div>
    </div>
    @include('layouts.vendor-scripts')
</body>

</html>
