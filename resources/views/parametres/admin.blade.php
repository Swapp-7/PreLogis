@extends('layouts.app')

@section('title', 'Paramètres du compte administrateur')

@section('content')
<div class="parametres-container">
    <div class="header-container">
        <h1 class="page-title">Paramètres du compte administrateur</h1>
        <a href="{{ route('parametres') }}" class="btn-retour">
            <i class="fas fa-arrow-left"></i> Retour aux paramètres
        </a>
    </div>
    
    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert-error">
        {{ session('error') }}
    </div>
    @endif
   
    
    <div class="section-container">
        <div class="section-header">
            <h2>Modifier l'adresse email</h2>
        </div>
        
        <div class="form-container">
            <form action="{{ route('parametres.admin.updateEmail') }}" method="POST" class="form-admin">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="info-value">identifiant : {{$admin->NOMUTILISATEUR}}</label>
                    
                </div>
                <div class="form-group">
                    <label for="email">Nouvelle adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $admin->EMAIL) }}" required maxlength="255">
                    @if ($errors->has('email'))
                        <span class="form-error">{{ $errors->first('email') }}</span>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="password">Confirmez votre mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    @if ($errors->has('password'))
                        <span class="form-error">{{ $errors->first('password') }}</span>
                    @endif
                    <span class="form-help">Pour des raisons de sécurité, veuillez entrer votre mot de passe actuel pour confirmer le changement.</span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-valider">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .parametres-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .page-title {
        color: #FDC11F;
        font-size: 32px;
        margin: 0;
        border-bottom: 2px solid #FDC11F;
        padding-bottom: 10px;
    }
    
    .btn-retour {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: rgba(253, 193, 31, 0.2);
        color: #FDC11F;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid rgba(253, 193, 31, 0.5);
    }
    
    .btn-retour:hover {
        background-color: #FDC11F;
        color: #112233;
    }
    
    .section-container {
        background-color: #112233;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(253, 193, 31, 0.2);
        margin-bottom: 30px;
    }
    
    .section-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .section-header h2 {
        color: #FFFFFF;
        margin: 0;
        font-size: 20px;
    }
    
    .info-container {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .info-item {
        display: flex;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
    }
    
    .info-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-label {
        width: 40%;
        color: #CDCBCE;
        font-weight: 500;
    }
    
    .info-value {
        width: 60%;
        color: #FDC11F;
        font-weight: 600;
    }
    
    .alert-success {
        background-color: rgba(46, 204, 113, 0.2);
        border: 1px solid rgba(46, 204, 113, 0.5);
        color: #2ecc71;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background-color: rgba(231, 76, 60, 0.2);
        border: 1px solid rgba(231, 76, 60, 0.5);
        color: #e74c3c;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .form-container {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #CDCBCE;
        font-weight: 500;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: rgba(255, 255, 255, 0.1);
        color: #FFFFFF;
    }
    
    .form-error {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 5px;
        display: block;
    }
    
    .form-help {
        color: #95a5a6;
        font-size: 0.8rem;
        margin-top: 5px;
        display: block;
        font-style: italic;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .btn-valider {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        padding: 10px 20px;
        border-radius: 5px;
        border: 1px solid rgba(46, 204, 113, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-valider:hover {
        background-color: #2ecc71;
        color: #112233;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .parametres-container {
            padding: 15px;
        }
        
        .header-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .btn-retour {
            align-self: flex-start;
        }
    }
</style>
@endsection
