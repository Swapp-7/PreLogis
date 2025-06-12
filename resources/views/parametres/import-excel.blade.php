@extends('layouts.app')

@section('title', 'Importation Excel - Résidents')

@section('content')
<div class="import-excel-container">
    <div class="page-header">
        <h1>Importation Excel - Résidents</h1>
        <a href="{{ route('parametres') }}" class="btn-return">
            <i class="fas fa-arrow-left"></i> Retour aux paramètres
        </a>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="import-form-container">
        <form method="POST" action="{{ route('parametres.import-excel.process') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-file-excel"></i>
                    <h3>Importation de résidents depuis Excel</h3>
                </div>
                
                <div class="import-instructions">
                    <div class="instruction-card">
                        <h4><i class="fas fa-info-circle"></i> Instructions d'importation</h4>
                        <ul>
                            <li>Téléchargez le modèle Excel ci-dessous</li>
                            <li>Remplissez le fichier avec les données des résidents</li>
                            <li>Respectez le format des colonnes (nom, prénom, email, etc.)</li>
                            <li>Les dates doivent être au format AAAA-MM-JJ ou JJ/MM/AAAA</li>
                            <li>Tous les champs marqués comme obligatoires doivent être remplis</li>
                            <li>Chaque ligne peut spécifier un bâtiment et une chambre différente</li>
                        </ul>
                    </div>
                    
                    <div class="format-requirements">
                        <h4><i class="fas fa-check-circle"></i> Formats requis</h4>
                        <div class="format-grid">
                            <div class="format-item">
                                <strong>Nom/Prénom :</strong>
                                <span>Lettres, espaces, tirets et apostrophes uniquement</span>
                                <em>Ex: Jean-Pierre, Marie-Claire</em>
                            </div>
                            <div class="format-item">
                                <strong>Email :</strong>
                                <span>Format email valide</span>
                                <em>Ex: jean.dupont@email.com</em>
                            </div>
                            <div class="format-item">
                                <strong>Téléphone :</strong>
                                <span>8 à 20 chiffres, avec espaces/tirets/points autorisés</span>
                                <em>Ex: 01.23.45.67.89, 01-23-45-67-89, +33123456789</em>
                            </div>
                            <div class="format-item">
                                <strong>Date naissance :</strong>
                                <span>DD/MM/YYYY ou YYYY-MM-DD</span>
                                <em>Ex: 15/01/2000, 2000-01-15</em>
                            </div>
                            <div class="format-item">
                                <strong>Bâtiment :</strong>
                                <span>Lettres et/ou chiffres (identifiant du bâtiment)</span>
                                <em>Ex: A, B, C, E, B1, C2</em>
                            </div>
                            <div class="format-item">
                                <strong>Chambre :</strong>
                                <span>Numéro entier positif</span>
                                <em>Ex: 101, 205, 301</em>
                            </div>
                            <div class="format-item">
                                <strong>Code postal :</strong>
                                <span>4 à 6 chiffres</span>
                                <em>Ex: 75001, 69000</em>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="excel_file">Fichier Excel :</label>
                        <div class="file-upload-container">
                            <input type="file" class="form-control" id="excel_file" name="excel_file" 
                                   accept=".xlsx,.xls,.csv" required>
                            <div class="file-upload-info">
                                <i class="fas fa-upload"></i>
                                <span>Formats acceptés : .xlsx, .xls, .csv (max 5MB)</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="template-download">
                    <a href="{{ route('parametres.import-excel.template') }}" class="btn btn-outline">
                        <i class="fas fa-download"></i> Télécharger le modèle Excel
                    </a>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-import"></i> Importer les résidents
                </button>
            </div>
        </form>
    </div>

    @if (session('import_results'))
        <div class="import-results">
            <h3><i class="fas fa-chart-bar"></i> Résultats de l'importation</h3>
            
            <div class="results-summary">
                @php
                    $successCount = collect(session('import_results'))->where('status', 'success')->count();
                    $errorCount = collect(session('import_results'))->where('status', 'error')->count();
                @endphp
                
                <div class="summary-card success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ $successCount }} Succès</span>
                </div>
                
                <div class="summary-card error">
                    <i class="fas fa-times-circle"></i>
                    <span>{{ $errorCount }} Erreurs</span>
                </div>
            </div>
            
            <div class="results-details">
                @foreach (session('import_results') as $result)
                    <div class="result-item {{ $result['status'] }}">
                        <i class="fas {{ $result['status'] === 'success' ? 'fa-check' : 'fa-times' }}"></i>
                        <span class="row-number">Ligne {{ $result['row'] }}:</span>
                        <span class="message">{{ $result['message'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
    .import-excel-container {
        padding: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #FDC11F;
    }
    
    .page-header h1 {
        color: #FDC11F;
        font-size: 28px;
        margin: 0;
    }
    
    .btn-return {
        background-color: rgba(253, 193, 31, 0.2);
        color: #FDC11F;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        border: 1px solid rgba(253, 193, 31, 0.5);
        transition: all 0.3s ease;
    }
    
    .btn-return:hover {
        background-color: #FDC11F;
        color: #112233;
    }
    
    .import-form-container {
        background-color: #112233;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        color: #FDC11F;
    }
    
    .section-header i {
        font-size: 24px;
        margin-right: 15px;
    }
    
    .section-header h3 {
        margin: 0;
        font-size: 20px;
    }
    
    .import-instructions {
        margin-bottom: 30px;
    }
    
    .instruction-card {
        background-color: rgba(253, 193, 31, 0.1);
        border: 1px solid rgba(253, 193, 31, 0.3);
        border-radius: 6px;
        padding: 20px;
    }
    
    .instruction-card h4 {
        color: #FDC11F;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .instruction-card h4 i {
        margin-right: 10px;
    }
    
    .instruction-card ul {
        color: #CDCBCE;
        margin: 0;
        padding-left: 20px;
    }
    
    .instruction-card li {
        margin-bottom: 8px;
        line-height: 1.5;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        color: #FDC11F;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .file-upload-container {
        position: relative;
    }
    
    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid rgba(253, 193, 31, 0.3);
        border-radius: 6px;
        background-color: rgba(255, 255, 255, 0.05);
        color: #FFFFFF;
        font-size: 16px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #FDC11F;
        box-shadow: 0 0 0 3px rgba(253, 193, 31, 0.1);
    }
    
    .file-upload-info {
        display: flex;
        align-items: center;
        margin-top: 8px;
        color: #CDCBCE;
        font-size: 14px;
    }
    
    .file-upload-info i {
        margin-right: 8px;
        color: #FDC11F;
    }
    
    .template-download {
        margin: 20px 0;
    }
    
    .btn {
        padding: 12px 25px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        border: none;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .btn i {
        margin-right: 8px;
    }
    
    .btn-outline {
        background-color: transparent;
        color: #FDC11F;
        border: 2px solid #FDC11F;
    }
    
    .btn-outline:hover {
        background-color: #FDC11F;
        color: #112233;
    }
    
    .btn-primary {
        background-color: #FDC11F;
        color: #112233;
    }
    
    .btn-primary:hover {
        background-color: #E5AD1A;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(253, 193, 31, 0.3);
    }
    
    .form-actions {
        text-align: center;
        margin-top: 30px;
    }
    
    .alert {
        padding: 15px 20px;
        border-radius: 6px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .alert ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .alert-success {
        background-color: rgba(76, 175, 80, 0.2);
        border: 1px solid rgba(76, 175, 80, 0.5);
        color: #4CAF50;
    }
    
    .alert-warning {
        background-color: rgba(255, 193, 7, 0.2);
        border: 1px solid rgba(255, 193, 7, 0.5);
        color: #FFC107;
    }
    
    .alert-danger, .alert-error {
        background-color: rgba(244, 67, 54, 0.2);
        border: 1px solid rgba(244, 67, 54, 0.5);
        color: #F44336;
    }
    
    .import-results {
        background-color: #112233;
        border-radius: 8px;
        padding: 25px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .import-results h3 {
        color: #FDC11F;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .import-results h3 i {
        margin-right: 10px;
    }
    
    .results-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .summary-card {
        flex: 1;
        padding: 15px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        font-weight: 600;
    }
    
    .summary-card i {
        font-size: 20px;
        margin-right: 10px;
    }
    
    .summary-card.success {
        background-color: rgba(76, 175, 80, 0.2);
        border: 1px solid rgba(76, 175, 80, 0.5);
        color: #4CAF50;
    }
    
    .summary-card.error {
        background-color: rgba(244, 67, 54, 0.2);
        border: 1px solid rgba(244, 67, 54, 0.5);
        color: #F44336;
    }
    
    .results-details {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .result-item {
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }
    
    .result-item i {
        margin-right: 10px;
        width: 16px;
    }
    
    .result-item.success {
        background-color: rgba(76, 175, 80, 0.1);
        border-left: 4px solid #4CAF50;
        color: #4CAF50;
    }
    
    .result-item.error {
        background-color: rgba(244, 67, 54, 0.1);
        border-left: 4px solid #F44336;
        color: #F44336;
    }
    
    .row-number {
        font-weight: 600;
        margin-right: 10px;
        min-width: 80px;
    }
    
    .message {
        flex: 1;
        color: #CDCBCE;
    }
    
    /* Styles pour les formats requis */
    .format-requirements {
        margin-top: 20px;
        padding: 20px;
        background-color: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 8px;
    }
    
    .format-requirements h4 {
        color: #3B82F6;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .format-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
    }
    
    .format-item {
        padding: 12px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        border-left: 4px solid #3B82F6;
    }
    
    .format-item strong {
        display: block;
        color: #3B82F6;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .format-item span {
        display: block;
        color: #CDCBCE;
        font-size: 13px;
        margin-bottom: 4px;
    }
    
    .format-item em {
        display: block;
        color: #9CA3AF;
        font-size: 12px;
        font-style: italic;
    }
    
    @media (max-width: 768px) {
        .import-excel-container {
            padding: 15px;
        }
        
        .page-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        
        .results-summary {
            flex-direction: column;
        }
        
        .format-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
