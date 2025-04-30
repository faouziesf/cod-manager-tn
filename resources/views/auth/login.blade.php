<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($userType ?? 'user') }} - Connexion | COD Manager TN</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
        }
        .login-container {
            max-width: 450px;
            margin: 100px auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .card-header {
            background-color: #fff;
            border-bottom: none;
            text-align: center;
            padding: 2rem 1rem 1rem;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .btn-primary {
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
        }
        .form-control {
            padding: 0.75rem;
            font-size: 1rem;
            border-radius: 0.375rem;
        }
        .invalid-feedback {
            font-size: 80%;
        }
        .user-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 15px;
            padding: 5px 15px;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .user-type-admin {
            background-color: #e74a3b;
            color: white;
        }
        .user-type-superadmin {
            background-color: #6f42c1;
            color: white;
        }
        .user-type-manager {
            background-color: #4e73df;
            color: white;
        }
        .user-type-employee {
            background-color: #1cc88a;
            color: white;
        }
        .user-type-user {
            background-color: #f6c23e;
            color: white;
        }
        .input-group-text {
            background-color: #f8f9fc;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #bac8f3;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card">
                    <div class="card-header position-relative">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22100%22%20height%3D%22100%22%20viewBox%3D%220%200%20100%20100%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20fill%3D%22%234e73df%22%20d%3D%22M30%2C20%20L70%2C20%20L70%2C80%20L30%2C80%20Z%22%20%2F%3E%3Cpath%20fill%3D%22%23f6c23e%22%20d%3D%22M20%2C30%20L80%2C30%20L80%2C70%20L20%2C70%20Z%22%20%2F%3E%3C%2Fsvg%3E';">
                        <h3 class="mb-3">COD Manager TN</h3>
                        <h5 class="text-muted mb-4">Connexion {{ ucfirst($userType ?? '') }}</h5>
                        <span class="user-type-badge user-type-{{ $userType ?? 'user' }}">{{ ucfirst($userType ?? 'Utilisateur') }}</span>
                    </div>
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route($userType . '.login') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label">Adresse e-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Connexion
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center bg-light py-3">
                        <div class="small text-muted">&copy; {{ date('Y') }} COD Manager TN - Tous droits réservés</div>
                    </div>
                </div>
                
                @if(app()->environment('local'))
                <div class="mt-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Comptes de démonstration</h6>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rôle</th>
                                            <th>Email</th>
                                            <th>Mot de passe</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @if($userType === 'admin' || $userType === 'superadmin')
                                        <tr>
                                            <td>Admin</td>
                                            <td>admin@example.com</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Super Admin</td>
                                            <td>superadmin@example.com</td>
                                            <td>password</td>
                                        </tr>
                                        @elseif($userType === 'manager' || $userType === 'employee')
                                        <tr>
                                            <td>Manager</td>
                                            <td>manager@cod.tn</td>
                                            <td>password</td>
                                        </tr>
                                        <tr>
                                            <td>Employé</td>
                                            <td>employee1@cod.tn</td>
                                            <td>password</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>