<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .auth-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .success-message {
            color: #28a745;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .loading {
            display: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Reset Password</h2>
            <p class="text-muted">Masukkan password baru Anda</p>
        </div>

        <form id="resetPasswordForm">
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            
            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       required 
                       minlength="12"
                       placeholder="Minimal 12 karakter dengan kombinasi huruf, angka, dan simbol">
                <div class="error-message" id="password-error"></div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       required
                       placeholder="Ulangi password baru">
                <div class="error-message" id="password_confirmation-error"></div>
            </div>

            <div class="mb-3">
                <div class="form-text">
                    <small>
                        Password harus minimal 12 karakter dan mengandung:
                        <ul>
                            <li>Huruf besar dan kecil</li>
                            <li>Angka</li>
                            <li>Karakter simbol</li>
                        </ul>
                    </small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                <span class="submit-text">Reset Password</span>
                <span class="loading">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Memproses...
                </span>
            </button>

            <div class="mt-3 text-center">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </div>

            <div id="form-message" class="mt-3"></div>
        </form>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            // Set CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                $('.error-message').text('');
                $('#form-message').html('');
                
                // Show loading state
                $('#submitBtn').prop('disabled', true);
                $('.submit-text').hide();
                $('.loading').show();

                const formData = {
                    token: $('input[name="token"]').val(),
                    email: $('input[name="email"]').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val()
                };

                // Validate password strength client-side
                if (!validatePassword(formData.password)) {
                    $('#password-error').text('Password tidak memenuhi persyaratan keamanan');
                    resetButton();
                    return;
                }

                if (formData.password !== formData.password_confirmation) {
                    $('#password_confirmation-error').text('Password tidak cocok');
                    resetButton();
                    return;
                }

                $.ajax({
                    url: '{{ route("password.update") }}',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.ok) {
                            $('#form-message').html(
                                '<div class="alert alert-success">' + response.message + '</div>'
                            );
                            
                            // Redirect to login after 2 seconds
                            setTimeout(function() {
                                window.location.href = '{{ route("login") }}';
                            }, 2000);
                        } else {
                            $('#form-message').html(
                                '<div class="alert alert-danger">' + response.message + '</div>'
                            );
                        }
                        resetButton();
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan. Silakan coba lagi.';
                        
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors) {
                                // Display field-specific errors
                                Object.keys(errors).forEach(function(field) {
                                    $('#' + field + '-error').text(errors[field][0]);
                                });
                                message = 'Periksa kembali data yang Anda masukkan.';
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        $('#form-message').html(
                            '<div class="alert alert-danger">' + message + '</div>'
                        );
                        resetButton();
                    }
                });
            });

            function validatePassword(password) {
                // Check minimum length
                if (password.length < 12) return false;
                
                // Check for uppercase letters
                if (!/[A-Z]/.test(password)) return false;
                
                // Check for lowercase letters
                if (!/[a-z]/.test(password)) return false;
                
                // Check for numbers
                if (!/\d/.test(password)) return false;
                
                // Check for special characters
                if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) return false;
                
                return true;
            }

            function resetButton() {
                $('#submitBtn').prop('disabled', false);
                $('.submit-text').show();
                $('.loading').hide();
            }

            // Real-time password validation
            $('#password').on('input', function() {
                const password = $(this).val();
                const errorElement = $('#password-error');
                
                if (password.length > 0 && !validatePassword(password)) {
                    errorElement.text('Password harus minimal 12 karakter dengan huruf besar, kecil, angka, dan simbol');
                } else {
                    errorElement.text('');
                }
            });

            // Real-time password confirmation validation
            $('#password_confirmation').on('input', function() {
                const password = $('#password').val();
                const confirmation = $(this).val();
                const errorElement = $('#password_confirmation-error');
                
                if (confirmation.length > 0 && password !== confirmation) {
                    errorElement.text('Password tidak cocok');
                } else {
                    errorElement.text('');
                }
            });
        });
    </script>
</body>
</html>