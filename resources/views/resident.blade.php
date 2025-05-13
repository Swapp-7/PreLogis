@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/resident.css') }}">
@php
    $resident = $chambre->resident;
@endphp
@if($resident)
    @include('partials.modal_depart')
@endif


    <div class="resident-container">
        <div class="navigation">
            <a href="{{ route('chambre', ['IdBatiment' => $chambre->IDBATIMENT]) }}" class="btn-return">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        
        @if($resident)
            <div class="page-header">
                <h1>Information du Résident</h1>
            </div>
            
            <div class="resident-layout">
                <!-- Colonne gauche - Photo et info principale -->
                <div class="resident-main">
                    <div class="resident-photo-container">
                        @if($resident->PHOTO == "photo" || $resident->PHOTO == null)
                            <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg"
                                alt="Photo par défaut" class="resident-photo">
                        @else
                            <img src="{{ asset('storage/' . $resident->PHOTO) }}"
                                alt="Photo du résident" class="resident-photo">
                        @endif
                        <div class="resident-badge">{{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</div>
                    </div>
                    
                    <div class="resident-identity">
                        <h2 class="resident-name">{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT }}</h2>
                        <p class="resident-mail"><i class="fas fa-envelope"></i> {{ $resident->MAILRESIDENT }}</p>
                        <p class="resident-phone"><i class="fas fa-phone"></i> {{ $resident->TELRESIDENT }}</p>
                        <p class="resident-nationality"><i class="fas fa-flag"></i> {{ $resident->NATIONALITE }}</p>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('modifierResident', ['idResident' => $resident->IDRESIDENT]) }}" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Modifier Résident
                        </a>
                        <button type="button" class="btn-action btn-schedule" onclick="openDepartModal()">
                            <i class="fas fa-calendar-check"></i> Planifier Départ
                        </button>
                        <a href="{{ route('supprimerResident', ['idResident' => $resident->IDRESIDENT]) }}" 
                           class="btn-action btn-delete" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce résident ? Cette action est irréversible.')">
                           <i class="fas fa-trash"></i> Supprimer Résident
                        </a>

                    </div>
                </div>
                
                <!-- Colonne centrale - Infos détaillées -->
                <div class="resident-details">
                    <div class="details-section">
                        <h3><i class="fas fa-user-graduate"></i> Études</h3>
                        <div class="details-grid">
                            <p><span>Établissement:</span> {{ $resident->ETABLISSEMENT }}</p>
                            <p><span>Année d'étude:</span> {{ $resident->ANNEEETUDE }}</p>
                        </div>
                    </div>
                    
                    <div class="details-section">
                        <h3><i class="fas fa-calendar-alt"></i> Dates</h3>
                        <div class="details-grid">
                            <p><span>Naissance:</span> {{ \Carbon\Carbon::parse($resident->DATENAISSANCE )->translatedFormat('d M Y') }}</p>
                            <p><span>Arrivée:</span> {{ \Carbon\Carbon::parse($resident->DATEINSCRIPTION )->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Colonne droite - Coordonnées -->
                <div class="resident-contacts">
                    <div class="details-section address-section">
                        <h3><i class="fas fa-home"></i> Adresse</h3>
                        <p>{{ $resident->adresse->ADRESSE }}</p>
                        <p>{{ $resident->adresse->CODEPOSTAL }} {{ $resident->adresse->VILLE }}</p>
                        <p>{{ $resident->adresse->PAYS }}</p>
                    </div>
                    
                    @if($resident->parents && count($resident->parents) > 0)
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
                        @if(in_array(pathinfo($fichier->CHEMINFICHIER, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                            <div class="file-preview">
                            <img src="{{ asset($fichier->CHEMINFICHIER) }}" alt="{{ $fichier->NOMFICHIER }}" class="file-image">
                            </div>
                        @elseif(in_array(pathinfo($fichier->CHEMINFICHIER, PATHINFO_EXTENSION), ['pdf']))
                            <div class="file-preview">
                            <embed src="{{ asset($fichier->CHEMINFICHIER) }}" type="application/pdf" class="file-pdf">
                            </div>
                        @endif
                        <div class="file-info">
                            <a href="{{ asset($fichier->CHEMINFICHIER) }}" target="_blank" class="file-link">{{ $fichier->NOMFICHIER }}</a>
                        </div>
                        <form action="{{ route('supprimerFichier', ['idFichier' => $fichier->IDFICHIER]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ? Cette action est irréversible.')">Supprimer</button>
                        </form>
                        </div>
                    @endforeach
                    </div>
                @else
                    <p class="no-files">Aucun documents</p>
                @endif
            </div>
            
            <!-- Section upload -->
            <div class="upload-section">
                <h2 class="section-title">Ajouter des fichiers</h2>
                <form action="{{ route('uploadFichier', ['idResident' => $resident->IDRESIDENT]) }}" method="POST" enctype="multipart/form-data" class="upload-form">
                    @csrf
                    <div class="form-group">
                        <label for="fichier">Choisir des fichiers :</label>
                        <input type="file" name="fichier[]" id="fichier" class="form-control" multiple required>
                    </div>
                    <button type="submit" class="btn-upload">
                        <i class="fas fa-upload"></i> Importer
                    </button>
                </form>
            </div>
        @else
            <div class="empty-room">
                <h1>Chambre libre</h1>
                <div class="empty-room-icon">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        @endif
    </div>

<style>

</style>
@endsection