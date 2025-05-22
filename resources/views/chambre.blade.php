@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chambre.css') }}">

<div class="chambre-container">
    <div class="page-header">
        <h1><i class="fas fa-door-open"></i> Chambres</h1>
        <p class="header-subtitle">Bâtiment {{ $chambres->first()->IDBATIMENT ?? '' }} - Sélectionnez une chambre pour voir les détails</p>
    </div>
    
    <div class="chambres-grid">
        @foreach ($chambres as $chambre)
            <a href="{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="chambre-card {{ $chambre->resident ? 'occupied' : 'vacant' }}">
                <div class="chambre-status">
                    @if($chambre->resident)
                        <i class="fas fa-user-check status-icon"></i>
                        <span class="status-text">Occupée</span>
                    @else
                        <i class="fas fa-door-open status-icon"></i>
                        <span class="status-text">Libre</span>
                    @endif
                </div>
                
                <div class="chambre-number">
                    @if ($chambre->NUMEROCHAMBRE < 10)
                        <span>{{ $chambre->IDBATIMENT }}0{{ $chambre->NUMEROCHAMBRE }}</span>
                    @else
                        <span>{{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</span>
                    @endif
                </div>
                
                @if($chambre->resident)
                    <div class="resident-info">
                        <div class="resident-photo">
                            @if($chambre->resident->PHOTO == "photo")
                                <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg" alt="Profile Placeholder">
                            @else
                                <img src="{{ asset('storage/' . $chambre->resident->PHOTO) }}" alt="Profile Photo">
                            @endif
                        </div>
                        <div class="resident-name">
                            {{ $chambre->resident->NOMRESIDENT }} {{ $chambre->resident->PRENOMRESIDENT }}
                        </div>
                    </div>
                @else
                    <div class="vacant-info">
                        <i class="fas fa-plus-circle"></i>
                        <span>Ajouter un résident</span>
                    </div>
                @endif
                
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection