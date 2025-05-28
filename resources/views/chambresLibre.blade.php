@extends('layouts.app')

@section('title', 'Chambres Libres')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chambres-libre.css') }}">

<div class="container">
    <div class="page-header">
        <h1>Gestion des chambres</h1>
    </div>
    
    <!-- Filter Panel -->
    <div class="filter-panel card mb-4">
        <div class="card-header">
            <h3>Filtrer par mois</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('filterDepartingResidents') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="month" class="mr-2">Mois:</label>
                    <select name="month" id="month" class="form-control">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="year" class="mr-2">Année:</label>
                    <select name="year" id="year" class="form-control">
                        @for ($i = now()->year; $i <= now()->year + 2; $i++)
                            <option value="{{ $i }}" {{ (isset($year) && $year == $i) ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </div>
    </div>

    <!-- Chambres Section -->
    <div class="chambres-container">
        <!-- Section 1: Chambres libres -->
        <div class="section">
            <h2>Chambres libres</h2>
            
            @php
                // Group rooms by building
                $chambresByBuilding = $chambresLibre->where('IDRESIDENT', null)
                    ->where(function($chambre) {
                        return $chambre->futureResidents->count() == 0;
                    })
                    ->groupBy('IDBATIMENT');
                
                $foundFree = $chambresByBuilding->count() > 0;
            @endphp
            
            @if ($foundFree)
                @foreach ($chambresByBuilding as $buildingId => $chambres)
                    <div class="building-section">
                        <h3 class="building-title">Bâtiment {{ $buildingId }}</h3>
                        <div class="chambres-grid">
                            @foreach ($chambres as $chambre)
                                <a href="{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="chambre-link">
                                    <div class="chambre free">
                                        <h3>Chambre {{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</h3>
                                        <p class="status">Statut: <span class="badge badge-success">Libre</span></p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-message">
                    <p>Aucune chambre libre pour le moment</p>
                </div>
            @endif
        </div>

        <!-- Section 2: Résidents qui partent ce mois-ci -->
        <div class="section">
            <h2>Résidents qui partent ce mois-ci ou avant</h2>
            
            @php
                // Create a date object from the selected month and year
                $selectedDate = Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();
                
                // Group rooms by building for departing residents
                $departingByBuilding = $chambresLibre->filter(function($chambre) use ($selectedDate) {
                    if (!$chambre->resident || !$chambre->resident->DATEDEPART || $chambre->futureResidents->count() > 0) {
                        return false;
                    }
                    
                    $departureDate = Carbon\Carbon::parse($chambre->resident->DATEDEPART);
                    return $departureDate->lte($selectedDate);
                })->groupBy('IDBATIMENT');
                
                $foundDeparting = $departingByBuilding->count() > 0;
            @endphp
            
            @if ($foundDeparting)
                @foreach ($departingByBuilding as $buildingId => $chambres)
                    <div class="building-section">
                        <h3 class="building-title">Bâtiment {{ $buildingId }}</h3>
                        <div class="chambres-grid">
                            @foreach ($chambres as $chambre)
                                <a href="{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="chambre-link">
                                    <div class="chambre departing">
                                        <h3>Chambre {{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</h3>
                                        <p class="status">Statut: <span class="badge badge-warning">Départ prévu</span></p>
                                        <div class="resident-info">
                                            <p>Résident: {{ $chambre->resident->NOMRESIDENT }} {{ $chambre->resident->PRENOMRESIDENT }}</p>
                                            <p>Date départ: {{ date('d/m/Y', strtotime($chambre->resident->DATEDEPART)) }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-message">
                    <p>Aucun résident ne part jusqu'à cette date</p>
                </div>
            @endif
        </div>
        
        <!-- Section 3: Futurs résidents qui partent -->
        <div class="section">
            <h2>Futurs résidents qui partent</h2>
            
            @php
                // Group rooms by building for future departing residents
                $futureDepartingByBuilding = $chambresLibre->filter(function($chambre) use ($selectedDate) {
                    if (!$chambre->futureResidents->last() || !$chambre->futureResidents->last()->DATEDEPART) {
                        return false;
                    }
                    
                    $departureDate = Carbon\Carbon::parse($chambre->futureResidents->last()->DATEDEPART);
                    return $departureDate->lte($selectedDate);
                })->groupBy('IDBATIMENT');
                
                $foundFutureDeparting = $futureDepartingByBuilding->count() > 0;
            @endphp
            
            @if ($foundFutureDeparting)
                @foreach ($futureDepartingByBuilding as $buildingId => $chambres)
                    <div class="building-section">
                        <h3 class="building-title">Bâtiment {{ $buildingId }}</h3>
                        <div class="chambres-grid">
                            @foreach ($chambres as $chambre)
                                <a href="{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT, 'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" class="chambre-link">
                                    <div class="chambre future-departing">
                                        <h3>Chambre {{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}</h3>
                                        <p class="status">Statut: <span class="badge badge-info">Futur départ</span></p>
                                        <div class="resident-info">
                                            <p>Résident: {{ $chambre->futureResidents->last()->NOMRESIDENT }} {{ $chambre->futureResidents->last()->PRENOMRESIDENT }}</p>
                                            <p>Date départ: {{ date('d/m/Y', strtotime($chambre->futureResidents->last()->DATEDEPART)) }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-message">
                    <p>Aucun futur résident ne part jusqu'à cette date</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Add to your existing CSS */

</style>
@endsection