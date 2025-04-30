<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur | COD Manager TN</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 600px;
            text-align: center;
            padding: 3rem 2rem;
        }
        .error-icon {
            font-size: 5rem;
            color: #e74a3b;
            margin-bottom: 1.5rem;
        }
        .error-code {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        .error-message {
            font-size: 1.25rem;
            color: #5a5c69;
            margin-bottom: 2rem;
        }
        .back-button {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1 class="error-code">{{ $errorCode ?? '500' }}</h1>
                <p class="error-message">{{ $errorMessage ?? 'Une erreur inattendue s\'est produite.' }}</p>
                <div class="mt-4">
                    <a href="{{ $backUrl ?? url('/') }}" class="btn btn-primary back-button">
                        <i class="fas fa-arrow-left me-2"></i>{{ $backText ?? 'Retour à l\'accueil' }}
                    </a>
                </div>
            </div>
            <div class="card-footer text-center bg-light py-3">
                <div class="small text-muted">&copy; {{ date('Y') }} COD Manager TN - Tous droits réservés</div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>