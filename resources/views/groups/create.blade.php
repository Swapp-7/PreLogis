@extends('layouts.app')

@section('title', 'Créer un Groupe')

@section('content')
<link rel="stylesheet" href="{{ asset('css/groupe-create.css') }}">

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1><i class="fas fa-users-plus"></i> Créer un Nouveau Groupe</h1>
                <p class="subtitle">Ajoutez un nouveau groupe de résidents</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('groups.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux groupes
                </a>
            </div>
        </div>
    </div>
    
    @if ($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <strong>Erreurs détectées :</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
    
    <div class="form-container">
        <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- Section Photo et Informations principales -->
            <div class="form-layout">
                <div class="form-left">
                    <div class="photo-section">
                        <h3><i class="fas fa-camera"></i> Photo du groupe</h3>
                        <div class="photo-upload-area">
                            <div class="photo-preview">
                                <img id="photo-preview-group" src="https://cdn-icons-png.flaticon.com/512/166/166258.png" alt="Aperçu photo du groupe">
                                <div class="photo-badge">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="upload-controls">
                                <label for="photo" class="upload-btn">
                                    <i class="fas fa-upload"></i> Choisir une photo
                                </label>
                                <input type="file" id="photo" name="photo" accept="image/*" hidden>
                                <small class="upload-hint">Format: JPG, PNG, GIF (Max: 5MB)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-right">
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Informations générales</h3>
                        
                        <div class="form-group">
                            <label for="nom">Nom du groupe *</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Famille Dupont">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email de contact</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="contact@exemple.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="tel">Téléphone</label>
                                <input type="text" class="form-control" id="tel" name="tel" value="{{ old('tel') }}" required placeholder="01 23 45 67 89">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Adresse -->
            <div class="form-section address-section">
                <h3><i class="fas fa-map-marker-alt"></i> Adresse du groupe</h3>
                
                <div class="form-group">
                    <label for="adresse">Rue et numéro *</label>
                    <input type="text" class="form-control" id="adresse" name="adresse[adresse]" value="{{ old('adresse.adresse') }}" required placeholder="123 Rue de la Paix">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal">Code Postal *</label>
                        <input type="text" class="form-control" id="code_postal" name="adresse[code_postal]" value="{{ old('adresse.code_postal') }}" required placeholder="75001">
                    </div>
                    
                    <div class="form-group">
                        <label for="ville">Ville *</label>
                        <input type="text" class="form-control" id="ville" name="adresse[ville]" value="{{ old('adresse.ville') }}" required placeholder="Paris">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="pays">Pays *</label>
                    <input type="text" class="form-control" id="pays" name="adresse[pays]" value="{{ old('adresse.pays') ?? 'France' }}" required>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Créer le groupe
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aperçu photo avec drag & drop
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview-group');
    const uploadArea = document.querySelector('.photo-upload-area');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    uploadArea.classList.add('has-image');
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Drag & Drop pour la photo
    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        uploadArea.addEventListener('drop', handleDrop, false);
    }
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        uploadArea.classList.add('drag-hover');
    }
    
    function unhighlight(e) {
        uploadArea.classList.remove('drag-hover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            photoInput.files = files;
            const event = new Event('change', { bubbles: true });
            photoInput.dispatchEvent(event);
        }
    }
});
</script>
@endsection