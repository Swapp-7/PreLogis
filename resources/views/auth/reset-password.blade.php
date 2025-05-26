<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 login-container">
                <div class="login-header">
                    <h2>PreLogis</h2>
                    <p>Créer un nouveau mot de passe</p>
                </div>

                <form method="POST" action="{{ route('admin.password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ request('email') }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="password-confirm" name="password_confirmation" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<style>
    /* Même style que la page forgot-password */
    body {
        background-color: #20364B;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Roboto', sans-serif;
        color: #FFFFFF;
        margin: 0;
        padding: 0;
    }
    
    .login-container {
        max-width: 400px;
        padding: 30px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(253, 193, 31, 0.3);
    }
    
    .login-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(253, 193, 31, 0.3);
    }
    
    .login-header h2 {
        color: #FDC11F;
        font-family: 'Montserrat', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
        text-transform: uppercase;
    }
    
    .login-header p {
        color: #CDCBCE;
        font-size: 1rem;
        font-weight: 300;
    }
    
    .form-label {
        font-weight: 500;
        color: #FDC11F;
        font-size: 0.95rem;
        margin-bottom: 8px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid rgba(253, 193, 31, 0.5);
        border-radius: 6px;
        font-size: 0.95rem;
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #FDC11F;
        box-shadow: 0 0 5px rgba(253, 193, 31, 0.5);
        outline: none;
    }
    
    .btn-primary {
        width: 100%;
        padding: 12px 25px;
        background-color: #FDC11F;
        color: #20364B;
        font-size: 1rem;
        font-weight: 600;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Montserrat', sans-serif;
    }
    
    .btn-primary:hover {
        background-color: #e6ae15;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.2);
        color: #ff6b6b;
        border: 1px solid rgba(220, 53, 69, 0.5);
        border-radius: 6px;
    }
    
    .invalid-feedback {
        color: #ff6b6b;
    }
</style>