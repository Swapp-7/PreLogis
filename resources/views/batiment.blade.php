@extends('layouts.app')

@section('title', 'Bâtiments')

@section('content')
<link rel="stylesheet" href="{{ asset('css/batiment.css') }}">

<div class="batiment-container">
    <div class="page-header">
        <h1><i class="fas fa-building"></i> Bâtiments</h1>
        <p class="header-subtitle">Sélectionnez un bâtiment pour voir les chambres disponibles</p>
    </div>
    
    <div class="batiments-grid">
        @foreach ($batiments as $batiment)
            @if ($batiment->CAPACITE != 0)
            <a href="{{ route('chambre', ['IdBatiment' => $batiment->IDBATIMENT]) }}" class="batiment-card">
                <div class="batiment-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="batiment-info">
                    <h3>Bâtiment {{ $batiment->IDBATIMENT }}</h3>
                    <div class="capacity-meter">
                        <div class="meter-label">Capacité</div>
                        <div class="meter-value">{{$batiment->chambres->where('IDRESIDENT', '!=', null)->count()}}/{{ $batiment->CAPACITE }}</div>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            @endif
        @endforeach
    </div>
</div>
@endsection