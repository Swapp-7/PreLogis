@extends('layouts.app')

@section('title', 'Informations Résident')

@section('content')
@php
    $resident = $chambre->resident;
@endphp
@if ($resident && $resident->NOMRESIDENT =="Berthet" && $resident->PRENOMRESIDENT == "Mano")
    <link rel="stylesheet" href="{{ asset('css/resident-mano.css') }}">
    
@else
    <link rel="stylesheet" href="{{ asset('css/resident.css') }}">
@endif

@if($resident)
    @include('partials.modal_depart')
    @include('partials.modal_solde_compte')
@endif
@php
    $futureResidents = $chambre->futureResidents;
@endphp

@include('partials.modal_modifier-date', ['futureResidents' => $futureResidents])


    <div class="resident-container">
        <div class="navigation">
            <a href="{{ route('chambre', ['IdBatiment' => $chambre->IDBATIMENT]) }}" class="btn-return">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
        
        @if($resident)
            <div class="page-header">
                <h1>Information de l'Occupant</h1>
            </div>
            
            <div class="resident-layout">

                
                <!-- Colonne gauche - Photo et info principale -->
                <div class="resident-main">
                    <div class="resident-photo-container">
                        @if($resident->PHOTO == "photo" || !$resident->PHOTO)
                            @if($resident->TYPE == 'group')
                                <img src="https://cdn-icons-png.flaticon.com/512/166/166258.png" class="resident-photo" alt="Photo actuelle du groupe">
                            @else
                                <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg" class="resident-photo" alt="Photo actuelle">
                            @endif
                        @else
                            <img src="{{ asset('storage/' . $resident->PHOTO) }}" alt="Photo actuelle" class="resident-photo">
                        @endif
                        <div class="resident-badge">{{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</div>
                    </div>
                    
                    <div class="resident-identity">
                        @if($resident->TYPE == 'group')
                            <h2 class="resident-name">
                                <i class="fas fa-users"></i> 
                                {{ $resident->NOMRESIDENT }}
                                <span class="group-tag">Groupe</span>
                            </h2>
                        @else
                            <h2 class="resident-name">{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT }}</h2>
                        @endif
                        <p class="resident-mail"><i class="fas fa-envelope"></i> {{ $resident->MAILRESIDENT }}</p>
                        <p class="resident-phone"><i class="fas fa-phone"></i> {{ $resident->TELRESIDENT }}</p>
                        @if($resident->TYPE != 'group')
                            <p class="resident-nationality"><i class="fas fa-flag"></i> {{ $resident->NATIONALITE }}</p>
                        @endif
                    </div>
                    
                    <div class="action-buttons">
                        <a href="{{ route('modifierResident', ['idResident' => $resident->IDRESIDENT]) }}" class="btn-action btn-edit">
                            <i class="fas fa-edit"></i> Modifier Résident
                        </a>
                        <a href="javascript:void(0);" class="btn-action btn-schedule" onclick="openDepartModal()">
                            <i class="fas fa-calendar-check"></i> Planifier Départ
                        </a>
                        @if($resident->DATEDEPART)
                        <a href="javascript:void(0);" 
                           class="btn-action btn-pdf" 
                           onclick="openSoldeCompteModal()"
                           title="Générer le solde de tout compte en PDF">
                           <i class="fas fa-file-pdf"></i> Solde de Tout Compte
                        </a>
                        @endif
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
                            @if($resident->TYPE != 'group')
                                <p><span>Naissance:</span> {{ \Carbon\Carbon::parse($resident->DATENAISSANCE )->translatedFormat('d M Y') }}</p>
                            @endif
                            <p><span>Arrivée:</span> {{ \Carbon\Carbon::parse($resident->DATEINSCRIPTION )->translatedFormat('d M Y') }}</p>
                            @if($resident->DATEDEPART)
                                <p><span>Départ prévu:</span> {{ \Carbon\Carbon::parse($resident->DATEDEPART)->translatedFormat('d M Y') }}</p>
                            @endif
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
                    
                    @if($resident->TYPE != 'group' && $resident->parents && count($resident->parents) > 0)
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
                    <a href="{{ route('telechargerTousFichiers', ['idResident' => $resident->IDRESIDENT]) }}" class="btn-upload">
                        <i class="fas fa-download"></i> Télécharger tous
                    </a>
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
            <!-- Section futurs résidents -->
            @else
            <div class="empty-room">
                <h1>Chambre libre {{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</h1>
                <div class="empty-room-icon">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
            @endif
            <div class="future-residents-section">
                <h2 class="section-title">Futurs résidents prévus</h2>
                @php
                    $futureResidents = $chambre->futureResidents;
                @endphp
                
                @if($futureResidents && count($futureResidents) > 0)
                    <div class="future-residents-list">
                        @foreach($futureResidents as $futureResident)
                            <div class="future-resident-card" onclick="window.location='{{ route('getResident', ['IdResident' => $futureResident->IDRESIDENT]) }}'">
                                <div class="future-resident-info">
                                    <div class="future-resident-photo">
                                        @if($futureResident->PHOTO == "photo" || $futureResident->PHOTO == null)
                                            <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg"
                                                alt="Photo par défaut" class="resident-photo-small">
                                        @else
                                            <img src="{{ asset('storage/' . $futureResident->PHOTO) }}"
                                                alt="Photo du futur résident" class="resident-photo-small">
                                        @endif
                                    </div>
                                    <div class="future-resident-details">
                                        <h3>{{ $futureResident->NOMRESIDENT }} {{ $futureResident->PRENOMRESIDENT }}</h3>
                                        <p><i class="fas fa-phone"></i> {{ $futureResident->TELRESIDENT }}</p>
                                        <p><i class="fas fa-envelope"></i> {{ $futureResident->MAILRESIDENT }}</p>
                                        <p><i class="fas fa-calendar-check"></i> Arrivée prévue: {{ \Carbon\Carbon::parse($futureResident->DATEINSCRIPTION)->translatedFormat('d F Y') }}</p>
                                        @if($futureResident->DATEDEPART)
                                            <p><i class="fas fa-calendar-times"></i> Départ prévu: {{ \Carbon\Carbon::parse($futureResident->DATEDEPART)->translatedFormat('d F Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="future-resident-actions">
                                    <a href="{{ route('modifierResident', ['idResident' => $futureResident->IDRESIDENT]) }}" class="btn-action btn-sm" onclick="event.stopPropagation();">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="javascript:void(0);" class="btn-action btn-sm" onclick="event.stopPropagation(); openDateModal('{{ $futureResident->IDRESIDENT }}', '{{ $futureResident->NOMRESIDENT }} {{ $futureResident->PRENOMRESIDENT }}', '{{ $futureResident->DATEINSCRIPTION }}', '{{ $futureResident->DATEDEPART }}');">
                                        <i class="fas fa-calendar-alt"></i> Dates
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @php
                        // Check if last future resident has a departure date
                        $lastFutureResident = $futureResidents->last();
                        $canAddAfterFuture = $lastFutureResident && $lastFutureResident->DATEDEPART;
                    @endphp
                    
                    @if($canAddAfterFuture)
                    <div class="add-future-resident-container">
                        <a href="{{ route('nouveauResident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="btn-add-future">
                            <i class="fas fa-user-plus"></i> Ajouter un futur résident
                        </a>
                    </div>
                    @endif
                @else
                    <p class="no-future-resident">Aucun futur résident prévu pour cette chambre</p>
                        
                    @php
                        // Check if current resident has a departure date
                        $canAddAfterCurrent = $resident && $resident->DATEDEPART;
                    @endphp
                    
                    @if(!$resident || $canAddAfterCurrent)
                    <div class="add-future-resident-container">
                        <a href="{{ route('nouveauResident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="btn-add-future">
                            <i class="fas fa-user-plus"></i> Ajouter un résident
                        </a>
                    </div>
                    @endif
                @endif
            </div>
    </div>

<style>

</style>
@endsection