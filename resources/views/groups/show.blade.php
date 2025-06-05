@extends('layouts.app')

@section('title', 'Détails du Groupe')

@section('content')
<link rel="stylesheet" href="{{ asset('css/groupe-show.css') }}">

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1><i class="fas fa-users"></i> {{ $group->NOMRESIDENT }}</h1>
                <p class="subtitle">Détails et gestion du groupe</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('groups.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux groupes
                </a>
            </div>
        </div>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
    @endif

    <!-- Layout principal -->
    <div class="group-layout">
        <!-- Colonne de gauche - Photo et identité -->
        <div class="group-main">
            <div class="group-photo-container">
                @if($group->PHOTO == "photo" || !$group->PHOTO)
                    <div class="default-group-photo">
                        <i class="fas fa-users"></i>
                    </div>
                @else
                    <img src="{{ asset('storage/' . $group->PHOTO) }}" alt="Photo du groupe" class="group-photo">
                @endif
                <div class="group-badge">
                    <i class="fas fa-users"></i> Groupe
                </div>
            </div>

            <div class="group-identity">
                <h2 class="group-name">{{ $group->NOMRESIDENT }}</h2>
                <p><i class="fas fa-envelope"></i> {{ $group->MAILRESIDENT ?: 'Non renseigné' }}</p>
                <p><i class="fas fa-phone"></i> {{ $group->TELRESIDENT ?: 'Non renseigné' }}</p>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-primary" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <button type="button" class="btn btn-danger" onclick="openDeleteModal()">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>

        <!-- Colonne centrale - Détails -->
        <div class="group-details">
            <div class="details-section">
                <h3><i class="fas fa-map-marker-alt"></i> Adresse</h3>
                <div class="details-grid">
                    <p><span>Rue :</span> {{ $group->adresse->ADRESSE ?? 'Non définie' }}</p>
                    <p><span>Code postal :</span> {{ $group->adresse->CODEPOSTAL ?? 'Non défini' }}</p>
                    <p><span>Ville :</span> {{ $group->adresse->VILLE ?? 'Non définie' }}</p>
                    <p><span>Pays :</span> {{ $group->adresse->PAYS ?? 'Non défini' }}</p>
                </div>
            </div>

            <div class="details-section">
                <h3><i class="fas fa-info-circle"></i> Informations générales</h3>
                <div class="details-grid">
                    <p><span>Type :</span> Groupe de résidents</p>
                    <p><span>ID :</span> #{{ $group->IDRESIDENT }}</p>
                    <p><span>Statut :</span> 
                        @if($group->chambres->count() > 0)
                            <span class="status-badge active">Actif</span>
                        @else
                            <span class="status-badge inactive">Inactif</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Colonne droite - Chambres -->
        <div class="group-chambers">
            <div class="section-title">
                <i class="fas fa-bed"></i>
                <h3>Chambres occupées</h3>
                <span class="chambers-count">{{ $group->chambres->count() }}</span>
            </div>

            @if($group->chambres->isEmpty())
                <div class="empty-chambers">
                    <div class="empty-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <p>Aucune chambre assignée</p>
                    <a href="{{ route('batiment') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Assigner à une chambre
                    </a>
                </div>
            @else
                <div class="chambers-list">
                    @foreach($group->chambres as $chambre)
                    <div class="chamber-card">
                        <div class="chamber-info">
                            <div class="chamber-number">
                                <span class="building">Bât. {{ $chambre->IDBATIMENT }}</span>
                                <span class="room">Ch. {{ $chambre->NUMEROCHAMBRE }}</span>
                            </div>
                            <div class="chamber-details">
                                <p><i class="fas fa-calendar"></i> Depuis le {{ $group->DATEINSCRIPTION ? \Carbon\Carbon::parse($group->DATEINSCRIPTION)->format('d/m/Y') : 'Non défini' }}</p>
                            </div>
                        </div>
                        <div class="chamber-actions">
                            <a href="{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="btn btn-sm btn-secondary btn-eye">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-danger" 
                                onclick="event.preventDefault(); 
                                            if(confirm('Retirer ce groupe de cette chambre ?')) {
                                                 document.getElementById('remove-room-{{ $chambre->IDCHAMBRE }}').submit();
                                            }">
                                 <i class="fas fa-times"></i>
                            </a>
                            <form id="remove-room-{{ $chambre->IDCHAMBRE }}" 
                                    action="{{ route('groups.rooms.remove', ['id' => $group->IDRESIDENT, 'roomId' => $chambre->IDCHAMBRE]) }}" 
                                    method="POST" style="display: none;">
                                 @csrf
                                 @method('DELETE')
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal d'édition du groupe -->
<div class="modal" id="editGroupModal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Modifier le groupe
                </h5>
                <button type="button" class="close" onclick="closeEditModal()">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('groups.update', $group->IDRESIDENT) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-section">
                        <h4>Informations générales</h4>
                        
                        <div class="photo-upload-section">
                            <label for="photo_modal">Photo du groupe</label>
                            <div class="photo-upload-container">
                                <div class="upload-preview">
                                    @if($group->PHOTO == "photo" || !$group->PHOTO)
                                        <img src="https://cdn-icons-png.flaticon.com/512/166/166258.png" class="resident-photo" alt="Photo actuelle du groupe">
                                    @else
                                        <img src="{{ asset('storage/' . $group->PHOTO) }}" alt="Photo actuelle" class="resident-photo">
                                    @endif
                                </div>
                                <input type="file" class="form-control" id="photo_modal" name="photo" accept="image/*">
                                <small class="form-text">Taille maximale: 5MB. Formats acceptés: JPG, PNG, GIF</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nom">Nom du groupe *</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="{{ $group->NOMRESIDENT }}" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $group->MAILRESIDENT }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="tel">Téléphone</label>
                                <input type="text" class="form-control" id="tel" name="tel" value="{{ $group->TELRESIDENT }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4>Adresse</h4>
                        
                        <div class="form-group">
                            <label for="adresse_modal">Rue *</label>
                            <input type="text" class="form-control" id="adresse_modal" name="adresse[adresse]" value="{{ $group->adresse->ADRESSE ?? '' }}" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="code_postal_modal">Code Postal *</label>
                                <input type="text" class="form-control" id="code_postal_modal" name="adresse[code_postal]" value="{{ $group->adresse->CODEPOSTAL ?? '' }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="ville_modal">Ville *</label>
                                <input type="text" class="form-control" id="ville_modal" name="adresse[ville]" value="{{ $group->adresse->VILLE ?? '' }}" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="pays_modal">Pays *</label>
                            <input type="text" class="form-control" id="pays_modal" name="adresse[pays]" value="{{ $group->adresse->PAYS ?? '' }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="deleteModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmer la suppression
                </h5>
                <button type="button" class="close" onclick="closeDeleteModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le groupe <strong>{{ $group->NOMRESIDENT }}</strong> ?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Le groupe sera archivé et retiré de toutes les chambres. Cette action est irréversible.
                </div>
            </div>
            <div class="modal-footer">
                <form>
                    @csrf
                    <button type="button" class="btn btn-secondary btn-annuler" onclick="closeDeleteModal()">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                </form>
                
                <form action="{{ route('groups.destroy', $group->IDRESIDENT) }}" method="POST" class="d-inline" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Archiver et supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aperçu photo pour le modal
    const photoInput = document.getElementById('photo_modal');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview-modal').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});

// Fonctions pour les modals
function openEditModal() {
    document.getElementById('editGroupModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editGroupModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openDeleteModal() {
    document.getElementById('deleteModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Fermer les modals en cliquant à l'extérieur
window.onclick = function(event) {
    const editModal = document.getElementById('editGroupModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target == editModal) {
        closeEditModal();
    }
    if (event.target == deleteModal) {
        closeDeleteModal();
    }
}

// Fermer les modals avec la touche Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
        closeDeleteModal();
    }
});
</script>
@endsection