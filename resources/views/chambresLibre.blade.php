@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chambres-libre.css') }}">
    <div class="Titre">
        <h1>Chambres</h1>
    </div>
    <div class="filter-container">
        <form method="GET" action="{{ route('filterDepartingResidents') }}">
            <label for="month">Mois :</label>
            <select name="month" id="month" required>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <label for="year">Année :</label>
            <select name="year" id="year" required>
                @for ($i = now()->year; $i <= now()->year + 5; $i++)
                    <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>

            <button type="submit">Filtrer</button>
        </form>
    </div>
    
    <div class="Titre">
        <h2>Chambres disponibles et résidents partant en {{ \Carbon\Carbon::create()->month($selectedMonth)->year($selectedYear)->translatedFormat('F Y') }}</h2>
    </div>
    
    @if($chambres->isEmpty())
        <div class="no-results">
            <i class="fas fa-info-circle"></i>
            <p>Aucun résident ne part en {{ \Carbon\Carbon::create()->month($selectedMonth)->year($selectedYear)->translatedFormat('F Y') }}.</p>
        </div>
    @else
        <div class="card-container">
            @php
                $batimentActuel = null;
            @endphp
            
            @foreach ($chambres as $chambre)
            @if($chambre->IDBATIMENT != $batimentActuel)
                @php
                    $batimentActuel = $chambre->IDBATIMENT;
                @endphp
                @if (!$loop->first)
                    </div> <!-- Close previous building's container -->
                @endif
                <div class="batiment-container">
                    <h1 style="text-align: center; margin-top: 20px;">Bâtiment {{$chambre->IDBATIMENT}}</h1>
            @endif
            
            @php
                $shouldDisplay = true;
                $willBeEmpty = false;
                $emptyPeriodStart = null;
                $emptyPeriodEnd = null;
                
                $chambreKey = $chambre->IDBATIMENT . '-' . $chambre->NUMEROCHAMBRE;
                
                if($chambre->resident) {
                    $isLeaving = in_array($chambre->IDRESIDENT, $departingResidents);
                    
                    // Vérifier si un résident part bientôt et s'il y a une période de vacance suffisante
                    if ($isLeaving && $chambre->resident->DATEDEPART) {
                        $departDate = \Carbon\Carbon::parse($chambre->resident->DATEDEPART);
                        $emptyPeriodStart = $departDate->copy()->addDay(); // Commence à être libre le jour après le départ
                        
                        if (isset($futureResidentsInfo[$chambreKey]) && count($futureResidentsInfo[$chambreKey]) > 0) {
                            // Trouver le prochain futur résident (celui avec la date d'arrivée la plus proche)
                            $nextResidentDate = null;
                            
                            foreach ($futureResidentsInfo[$chambreKey] as $futureResident) {
                                $arrivalDate = \Carbon\Carbon::parse($futureResident['DATEINSCRIPTION']);
                                
                                if ($arrivalDate->greaterThanOrEqualTo($departDate) && 
                                    ($nextResidentDate === null || $arrivalDate->lessThan($nextResidentDate))) {
                                    $nextResidentDate = $arrivalDate;
                                }
                            }
                            
                            if ($nextResidentDate) {
                                $emptyPeriodEnd = $nextResidentDate->copy()->subDay(); // Libre jusqu'à la veille de l'arrivée
                                $emptyDays = $departDate->diffInDays($nextResidentDate);
                                
                                // La chambre est considérée comme libre si la période d'inoccupation est d'au moins 2 jours
                                $willBeEmpty = $emptyDays >= 2;
                            } else {
                                // Aucun prochain résident = libre indéfiniment
                                $willBeEmpty = true;
                                $emptyPeriodEnd = null;
                            }
                        } else {
                            // Pas de futur résident prévu = libre indéfiniment
                            $willBeEmpty = true;
                            $emptyPeriodEnd = null;
                        }
                    }
                } else {
                    // Chambre déjà libre
                    $willBeEmpty = true;
                    $emptyPeriodStart = \Carbon\Carbon::now();
                    
                    // Vérifier s'il y a un futur résident prévu
                    if (isset($futureResidentsInfo[$chambreKey]) && count($futureResidentsInfo[$chambreKey]) > 0) {
                        $nextResidentDate = null;
                        
                        foreach ($futureResidentsInfo[$chambreKey] as $futureResident) {
                            $arrivalDate = \Carbon\Carbon::parse($futureResident['DATEINSCRIPTION']);
                            
                            if ($nextResidentDate === null || $arrivalDate->lessThan($nextResidentDate)) {
                                $nextResidentDate = $arrivalDate;
                            }
                        }
                        
                        if ($nextResidentDate) {
                            $emptyPeriodEnd = $nextResidentDate->copy()->subDay();
                            $emptyDays = \Carbon\Carbon::now()->diffInDays($nextResidentDate);
                            
                            // La chambre est considérée comme libre si la période d'inoccupation est d'au moins 2 jours
                            $willBeEmpty = $emptyDays >= 2;
                        }
                    }
                }
                
                // Décider si on affiche cette chambre
                $shouldDisplay = $willBeEmpty;
            @endphp
            
            @if($shouldDisplay)
                @if($chambre->resident)
                    <div class="card {{ $isLeaving ? 'departing' : '' }}" onclick="window.location='{{ route('nouveauResident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}'">
                        @if($chambre->resident->PHOTO == "photo")
                            <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg" alt="Profile Placeholder" class="profile-photo">
                        @else
                            <img src="{{ asset('storage/' . $chambre->resident->PHOTO) }}" alt="Profile Photo" class="profile-photo">
                        @endif
                        @if ($chambre->NUMEROCHAMBRE < 10)
                            <h2>{{$chambre->IDBATIMENT}}0{{$chambre->NUMEROCHAMBRE}}</h2>
                        @else
                            <h2>{{$chambre->IDBATIMENT}}{{$chambre->NUMEROCHAMBRE}}</h2>
                        @endif
                        <p class="occupe">{{$chambre->resident->NOMRESIDENT }} {{$chambre->resident->PRENOMRESIDENT }}</p>
                        
                        @if($isLeaving && $chambre->resident->DATEDEPART)
                            <p class="depart-info">Départ: {{ \Carbon\Carbon::parse($chambre->resident->DATEDEPART)->translatedFormat('d F Y') }}</p>
                            @if($emptyPeriodStart && $emptyPeriodEnd)
                                <p class="disponible-info">Disponible du {{ $emptyPeriodStart->translatedFormat('d/m/Y') }} au {{ $emptyPeriodEnd->translatedFormat('d/m/Y') }}</p>
                            @elseif($emptyPeriodStart)
                                <p class="disponible-info">Disponible à partir du {{ $emptyPeriodStart->translatedFormat('d/m/Y') }}</p>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="card libre-card" onclick="window.location='{{ route('nouveauResident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}'">
                        @if ($chambre->NUMEROCHAMBRE < 10)
                            <h2>{{$chambre->IDBATIMENT}}0{{$chambre->NUMEROCHAMBRE}}</h2>
                        @else
                            <h2>{{$chambre->IDBATIMENT}}{{$chambre->NUMEROCHAMBRE}}</h2>
                        @endif
                        <p class="libre">Chambre libre</p>
                        @if($emptyPeriodEnd)
                            <p class="disponible-info">Disponible jusqu'au {{ $emptyPeriodEnd->translatedFormat('d/m/Y') }}</p>
                        @endif
                    </div>
                @endif
            @endif
            @endforeach
            </div> 
        </div>
    @endif
@endsection