@extends('layouts.app')

@section('title', 'Optimisation des Occupations')

@section('content')
<div class="parametres-container">
    <h1 class="page-title">Optimisation des Occupations</h1>
    
    <div class="optimisation-card">
        <div class="card-header">
            <i class="fas fa-database"></i>
            <h2>Nettoyage des données d'occupation</h2>
        </div>
        <div class="card-body">
            <p class="description">
                Cette fonctionnalité vous permet de supprimer les anciennes occupations et dates pour optimiser les performances du serveur.
                <br>
                <strong>Attention :</strong> Cette action est irréversible. Les données supprimées ne pourront pas être récupérées.
            </p>
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            
            <form action="{{ route('parametres.optimisation-occupation.optimiser') }}" method="POST" class="optimisation-form">
                @csrf
                <div class="form-group">
                    <label for="date_limite">Supprimer toutes les occupations antérieures au :</label>
                    <input type="date" id="date_limite" name="date_limite" class="form-control" 
                           value="{{ \Carbon\Carbon::now()->subWeeks(2)->format('Y-m-d') }}" 
                           max="{{ \Carbon\Carbon::now()->startOfWeek()->subDay()->format('Y-m-d') }}">
                    <small class="text-muted">La date doit être antérieure au début de la semaine actuelle ({{ \Carbon\Carbon::now()->startOfWeek()->format('d/m/Y') }})</small>
                    @error('date_limite')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <button type="submit" class="btn-optimiser">Nettoyer les données</button>
            </form>
        </div>
    </div>
    
    <a href="{{ route('parametres') }}" class="btn-retour">
        <i class="fas fa-arrow-left"></i> Retour aux paramètres
    </a>
</div>

<style>
    .parametres-container {
        padding: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .page-title {
        color: #FDC11F;
        font-size: 32px;
        margin-bottom: 30px;
        border-bottom: 2px solid #FDC11F;
        padding-bottom: 10px;
    }
    
    .optimisation-card {
        background-color: #112233;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        margin-bottom: 30px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .card-header {
        background-color: rgba(253, 193, 31, 0.1);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .card-header i {
        font-size: 24px;
        margin-right: 15px;
        color: #FDC11F;
    }
    
    .card-header h2 {
        font-size: 20px;
        margin: 0;
        color: #FFFFFF;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .description {
        color: #CDCBCE;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .optimisation-form {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 5px;
        border: 1px solid rgba(253, 193, 31, 0.1);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        color: #FFFFFF;
        margin-bottom: 8px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        background-color: #1A2A3A;
        border: 1px solid rgba(253, 193, 31, 0.2);
        color: #FFFFFF;
    }
    
    .btn-optimiser {
        background-color: rgba(253, 193, 31, 0.2);
        color: #FDC11F;
        padding: 12px 24px;
        border-radius: 5px;
        border: 1px solid rgba(253, 193, 31, 0.5);
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-optimiser:hover {
        background-color: #FDC11F;
        color: #112233;
    }
    
    .btn-retour {
        display: inline-block;
        color: #CDCBCE;
        text-decoration: none;
        margin-top: 20px;
        transition: color 0.3s ease;
    }
    
    .btn-retour:hover {
        color: #FDC11F;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .alert-success {
        background-color: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }
    
    .text-danger {
        color: #dc3545;
        display: block;
        margin-top: 5px;
        font-size: 0.9em;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .parametres-container {
            padding: 15px;
        }
        
        .page-title {
            font-size: 24px;
        }
    }
</style>
@endsection
