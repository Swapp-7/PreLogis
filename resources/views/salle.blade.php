@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/salle.css') }}">
<div class="page-container">
    <div class="header-section">
        <h1 class="page-title">Liste des Salles</h1>
        <p class="page-subtitle">Sélectionnez une salle pour gérer ses occupations ou consultez l'emploi du temps global</p>
    </div>
    
    <div class="actions-bar">
        <a href="{{ route('lesSalles') }}" class="action-button">
            <i class="fas fa-calendar-alt"></i> Voir l'emploi du temps complet
        </a>
    </div>
    
    <div class="salles-grid">
        @foreach($salles as $salle)
            <a href="{{ route('detailSalle', ['IdSalle' => $salle->IDSALLE]) }}" class="salle-card">
                <div class="card-icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="card-content">
                    <h3 class="salle-name">{{ $salle->LIBELLESALLE }}</h3>
                    <div class="card-actions">
                        <span class="btn-detail">Détails <i class="fas fa-chevron-right"></i></span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection

<style>
   
</style>