<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Login'])
    @include('layouts.head-css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

                                <a href="#" class="text-center mt-4 mb-0" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forget Password?</a>
                            </div> <!-- end col -->

                            <!-- Forgot Password Modal -->
                            <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="forgotPasswordModalLabel">Lupa Password</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="forgotPasswordForm">
                                                <div class="mb-3">
                                                    <label for="forgotEmail" class="form-label">Email</label>
                                                    <input type="email" 
                                                           class="form-control" 
                                                           id="forgotEmail" 
                                                           name="email" 
                                                           required 
                                                           placeholder="Masukkan email Anda">
                                                    <div class="form-text">
                                                        Kami akan mengirim link reset password ke email Anda.
                                                    </div>
                                                    <div class="error-message text-danger mt-1" id="forgot-email-error"></div>
                                                </div>
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary" id="forgotSubmitBtn">
                                                        <span class="submit-text">Kirim Link Reset</span>
                                                        <span class="loading d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                            Mengirim...
                                                        </span>
                                                    </button>
                                                </div>
                                                <div id="forgot-message" class="mt-3"></div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->
            @include('layouts.footer')
        </div>
    </div>
    @include('layouts.vendor-scripts')
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            // Set CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            $('#forgotPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors and messages
                $('#forgot-email-error').text('');
                $('#forgot-message').html('');
                
                // Show loading state
                $('#forgotSubmitBtn').prop('disabled', true);
                $('.submit-text').hide();
                $('.loading').removeClass('d-none');

                const email = $('#forgotEmail').val();

                // Basic email validation
                if (!validateEmail(email)) {
                    $('#forgot-email-error').text('Format email tidak valid');
                    resetForgotButton();
                    return;
                }

                $.ajax({
                    url: '{{ route("password.email") }}',
                    method: 'POST',
                    data: {
                        email: email
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.ok) {
                            $('#forgot-message').html(
                                '<div class="alert alert-success">' + response.message + '</div>'
                            );
                            
                            // Clear form
                            $('#forgotEmail').val('');
                            
                            // Close modal after 3 seconds
                            setTimeout(function() {
                                $('#forgotPasswordModal').modal('hide');
                            }, 3000);
                        } else {
                            $('#forgot-message').html(
                                '<div class="alert alert-danger">' + response.message + '</div>'
                            );
                        }
                        resetForgotButton();
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan. Silakan coba lagi.';
                        
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors && errors.email) {
                                $('#forgot-email-error').text(errors.email[0]);
                                message = 'Periksa kembali email yang Anda masukkan.';
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        $('#forgot-message').html(
                            '<div class="alert alert-danger">' + message + '</div>'
                        );
                        resetForgotButton();
                    }
                });
            });

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            function resetForgotButton() {
                $('#forgotSubmitBtn').prop('disabled', false);
                $('.submit-text').show();
                $('.loading').addClass('d-none');
            }

            // Clear messages when modal is closed
            $('#forgotPasswordModal').on('hidden.bs.modal', function() {
                $('#forgot-email-error').text('');
                $('#forgot-message').html('');
                $('#forgotEmail').val('');
                resetForgotButton();
            });
        });
    </script>
</body>

</html>
