
@extends('layouts.app')
@section('title', 'Archive')
@section('content')
@php
    $resident = $residentA 
@endphp
<link rel="stylesheet" href="{{ asset('css/resident-archive.css') }}">
<div class="archive-container">
    <div class="page-header">
        <div class="header-top">
            <a href="{{ route('archive') }}" class="btn-return">
                <i class="fas fa-arrow-left"></i> Retour aux archives
            </a>
        </div>
        @if($resident->isGroup())
            <h1>Archive du Groupe {{ $resident->NOMRESIDENTARCHIVE }}</h1>
            <div class="archive-type-indicator group-indicator">
                <i class="fas fa-users"></i> Groupe Archivé
            </div>
        @else
            <h1>Archive {{ $resident->NOMRESIDENTARCHIVE }} {{ $resident->PRENOMRESIDENTARCHIVE }}</h1>
            <div class="archive-type-indicator individual-indicator">
                <i class="fas fa-user"></i> Résident Individuel Archivé
            </div>
        @endif
    </div>
    
    <div class="resident-layout">
        <!-- Colonne gauche - Photo et info principale -->
        <div class="resident-main">
            <div class="resident-photo-container">
                @if($resident->PHOTOARCHIVE == "photo" || $resident->PHOTOARCHIVE == null)
                <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg"
                    alt="Photo par défaut" class="resident-photo">
                @else
                <img src="{{ asset('storage/' . $resident->PHOTOARCHIVE) }}"
                    alt="Photo {{ $resident->isGroup() ? 'du groupe' : 'du résident' }}" class="resident-photo">
                @endif
                
                @if($resident->isGroup())
                    <div class="resident-badge group-badge">
                        @php
                            $chambres = $resident->getChambresOccupees();
                        @endphp
                        @if(count($chambres) > 0)
                            <span class="chambres-count">{{ count($chambres) }} chambre(s)</span>
                        @else
                            <span>Aucune chambre</span>
                        @endif
                    </div>
                @else
                    <div class="resident-badge">
                        @if($resident->chambre)
                            {{ $resident->chambre->IDBATIMENT }}{{ $resident->chambre->NUMEROCHAMBRE }}
                        @else
                            <span>Chambre supprimée</span>
                        @endif
                    </div>
                @endif
            </div>
            
            <div class="resident-identity">
                @if($resident->isGroup())
                    <h2 class="resident-name">{{ $resident->NOMRESIDENTARCHIVE }}</h2>
                    <p class="group-description">Groupe de résidents</p>
                @else
                    <h2 class="resident-name">{{ $resident->NOMRESIDENTARCHIVE }} {{ $resident->PRENOMRESIDENTARCHIVE }}</h2>
                    @if($resident->NATIONALITEARCHIVE)
                        <p class="resident-nationality"><i class="fas fa-flag"></i> {{ $resident->NATIONALITEARCHIVE }}</p>
                    @endif
                @endif
                <p class="resident-mail"><i class="fas fa-envelope"></i> {{ $resident->MAILRESIDENTARCHIVE }}</p>
                <p class="resident-phone"><i class="fas fa-phone"></i> {{ $resident->TELRESIDENTARCHIVE }}</p>
            </div>
        </div>
        
        <!-- Colonne centrale - Infos détaillées -->
        <div class="resident-details">
            @if($resident->isGroup())
                <div class="details-section">
                    <h3><i class="fas fa-home"></i> Chambres Occupées</h3>
                    <div class="chambres-group-list">
                        @php
                            $chambres = $resident->getChambresOccupees();
                        @endphp
                        @if(count($chambres) > 0)
                            @foreach($chambres as $chambre)
                                <div class="chambre-item">
                                    <i class="fas fa-bed"></i>
                                    <span>{{ $chambre }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="no-data">Aucune chambre enregistrée pour ce groupe</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="details-section">
                    <h3><i class="fas fa-user-graduate"></i> Études</h3>
                    <div class="details-grid">
                        <p><span>Établissement:</span> {{ $resident->ETABLISSEMENTARCHIVE ?: 'Non renseigné' }}</p>
                        <p><span>Année:</span> {{ $resident->ANNEEETUDEARCHIVE ?: 'Non renseigné' }}</p>
                    </div>
                </div>
            @endif
            
            <div class="details-section">
                <h3><i class="fas fa-calendar-alt"></i> Dates</h3>
                <div class="details-grid">
                    @if(!$resident->isGroup() && $resident->DATENAISSANCEARCHIVE)
                        <p><span>Naissance:</span> {{ \Carbon\Carbon::parse($resident->DATENAISSANCEARCHIVE)->translatedFormat('d M Y') }}</p>
                    @endif
                    <p><span>Arrivée:</span> {{ \Carbon\Carbon::parse($resident->DATEINSCRIPTIONARCHIVE)->translatedFormat('d M Y') }}</p>
                    <p><span>Archivé le:</span> {{ \Carbon\Carbon::parse($resident->DATEARCHIVE)->translatedFormat('d M Y') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Colonne droite - Coordonnées -->
        <div class="resident-contacts">
            @if($resident->adresse)
                <div class="details-section address-section">
                    <h3><i class="fas fa-home"></i> Adresse</h3>
                    <p>{{ $resident->adresse->ADRESSE }}</p>
                    <p>{{ $resident->adresse->CODEPOSTAL }} {{ $resident->adresse->VILLE }}</p>
                    <p>{{ $resident->adresse->PAYS }}</p>
                </div>
            @endif
            
            @if(!$resident->isGroup() && $resident->parents && count($resident->parents) > 0)
            <div class="details-section">
                <h3><i class="fas fa-users"></i> Parents</h3>
                @foreach($resident->parents as $parent)
                <div class="parent-info">
                    <p><i class="fas fa-user"></i> {{ $parent->NOMPARENT}}</p>
                    <p><i class="fas fa-phone"></i> {{ $parent->TELPARENT}}</p>
                    <p><i class="fas fa-briefcase"></i> {{ $parent->PROFESSION}}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    
    <!-- Section des fichiers -->
    <div class="files-section">
        <h2 class="section-title">Documents</h2>
        @if($resident->fichiers && $resident->fichiers->count() > 0)
        <div class="file-gallery">
            @foreach($resident->fichiers as $fichier)
            <div class="file-item">
                @if(in_array(pathinfo($fichier->NOMFICHIER, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                    <div class="file-preview">
                        <img src="{{ route('viewFile', ['idFichier' => $fichier->IDFICHIER]) }}" alt="{{ $fichier->NOMFICHIER }}" class="file-image">
                    </div>
                @elseif(in_array(pathinfo($fichier->NOMFICHIER, PATHINFO_EXTENSION), ['pdf']))
                    <div class="file-preview">
                        <embed src="{{ route('viewFile', ['idFichier' => $fichier->IDFICHIER]) }}" type="application/pdf" class="file-pdf">
                    </div>
                @endif
                <div class="file-info">
                    <a href="{{ route('viewFile', ['idFichier' => $fichier->IDFICHIER]) }}" target="_blank" class="file-link">{{ $fichier->NOMFICHIER }}</a>
                </div>
                <form action="{{ route('supprimerFichier', ['idFichier' => $fichier->IDFICHIER]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ? Cette action est irréversible.')">Supprimer</button>
                </form>
            </div>
            @endforeach
        </div>
        <div class="files-section-footer">
            <a href="{{ route('telechargerTousFichiers', ['idResident' => $resident->IDRESIDENTARCHIVE]) }}" class="btn-upload">
                <i class="fas fa-download"></i> Télécharger tous
            </a>
        </div>
        @else
            <p class="no-files">Aucun documents</p>
        @endif
    </div>
</div>

<style>
/* Styles pour le bouton retour - même style que la page résident */
.header-top {
    margin: 20px 0;
    text-align: left;
}

.btn-return {
    display: inline-block;
    padding: 8px 16px;
    background-color: rgba(255, 255, 255, 0.15);
    color: #FDC11F;
    text-decoration: none;
    border-radius: 25px;
    font-family: 'Roboto', sans-serif;
    font-weight: 500;
    border: 2px solid #FDC11F;
    transition: all 0.3s ease;
}

.btn-return:hover {
    background-color: rgba(253, 193, 31, 0.2);
    transform: translateX(-3px);
    text-decoration: none;
}

/* Styles pour le bouton télécharger tous - même style que la page résident */
.btn-upload {
    align-self: center !important;
    background-color: #FDC11F !important;
    color: #20364B !important;
    border: none !important;
    padding: 10px 20px !important;
    border-radius: 25px !important;
    cursor: pointer !important;
    font-size: 1rem !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    text-decoration: none !important;
}

.btn-upload:hover {
    background-color: #e6ae15 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
    text-decoration: none !important;
}

.files-section-footer {
    display: flex !important;
    justify-content: center !important;
    margin-top: 20px !important;
}

.files-section-footer .btn-upload {
    text-decoration: none !important;
    background-color: #FDC11F !important;
    color: #20364B !important;
}

.files-section-footer .btn-upload:hover {
    background-color: #e6ae15 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2) !important;
}

.files-section-footer i {
    margin-right: 8px !important;
}

/* Responsive pour les boutons */
@media (max-width: 768px) {
    .btn-return {
        padding: 6px 12px;
        font-size: 0.9rem;
    }
    
    .btn-upload {
        padding: 8px 16px !important;
        font-size: 0.9rem !important;
    }
    
    .header-top {
        margin: 15px 0;
    }
}

/* Styles spécifiques pour les groupes archivés */
.archive-type-indicator {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 20px;
}

.group-indicator {
    background-color: #FDC11F;
    color: #20364B;
}

.individual-indicator {
    background-color: #20364B;
    color: #fff;
    border: 2px solid #FDC11F;
}

.archive-type-indicator i {
    margin-right: 6px;
    font-size: 1rem;
}

.group-badge {
    background-color: #FDC11F !important;
    color: #20364B !important;
    font-weight: 600;
}

.chambres-count {
    font-size: 0.85rem;
    font-weight: 600;
}

.group-description {
    color: #FDC11F;
    font-style: italic;
    font-size: 1rem;
    margin-bottom: 10px;
}

.chambres-group-list {
    display: grid;
    gap: 8px;
    margin-top: 10px;
}

.chambre-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background-color: rgba(253, 193, 31, 0.1);
    border: 1px solid rgba(253, 193, 31, 0.3);
    border-radius: 8px;
    color: #FDC11F;
    font-weight: 500;
}

.chambre-item i {
    margin-right: 8px;
    color: #FDC11F;
}

.no-data {
    color: rgba(255, 255, 255, 0.6);
    font-style: italic;
    text-align: center;
    padding: 20px;
}

/* Responsive adjustments for groups */
@media (max-width: 768px) {
    .archive-type-indicator {
        font-size: 0.8rem;
        padding: 6px 12px;
    }
    
    .chambres-group-list {
        grid-template-columns: 1fr;
    }
    
    .chambre-item {
        padding: 6px 10px;
        font-size: 0.9rem;
    }
}

/* Override existing styles for better group display */
.resident-name {
    color: #FDC11F !important;
}

.details-section h3 {
    border-bottom: 2px solid #FDC11F;
    padding-bottom: 5px;
    margin-bottom: 15px;
}
</style>
@endsection
