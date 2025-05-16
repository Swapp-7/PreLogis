<!-- filepath: /home/berman/stage/PreLogis/PreLogis/resources/views/planning-resident.blade.php -->
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/planning-resident.css') }}">

<div class="planning-container">
    <div class="planning-header">
        <h1 class="planning-title">Planning des résidents</h1>
        <p class="planning-subtitle">Suivi des arrivées et départs des résidents</p>
    </div>

    <!-- Contrôles de navigation et filtres -->
    <div class="planning-controls">
        <div class="month-navigation">
            <a href="{{ route('planning.resident', ['month' => Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->month, 'year' => Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->year]) }}" class="btn-outline">
                <i class="fas fa-chevron-left"></i> Mois précédent
            </a>
            
            <span class="current-month">{{ Carbon\Carbon::createFromDate($year, $month, 1)->locale('fr')->translatedFormat('F Y') }}</span>
            
            <a href="{{ route('planning.resident', ['month' => Carbon\Carbon::createFromDate($year, $month, 1)->addMonth()->month, 'year' => Carbon\Carbon::createFromDate($year, $month, 1)->addMonth()->year]) }}" class="btn-outline">
                Mois suivant <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        
        <div class="filter-controls">
            <a href="{{ route('planning.resident.export') }}" class="btn-outline">
                <i class="fas fa-file-excel"></i> Exporter en Excel
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="summary-dashboard">
        <div class="summary-cards">
            @php
                $arrivees = collect($residents)->filter(function($resident) use ($startOfMonth, $endOfMonth) {
                    return $resident->DATEINSCRIPTION && 
                           Carbon\Carbon::parse($resident->DATEINSCRIPTION)->between($startOfMonth, $endOfMonth);
                });
                
                $departs = collect($residents)->filter(function($resident) use ($startOfMonth, $endOfMonth) {
                    return $resident->DATEDEPART && 
                           Carbon\Carbon::parse($resident->DATEDEPART)->between($startOfMonth, $endOfMonth);
                });
                
                $countChambres = isset($chambres) ? count($chambres) : 0;
                $countOccupation = isset($chambres) ? count($chambres->whereNotNull('IDRESIDENT')) : 0;
            @endphp
            
            <div class="summary-card arrivals-card">
                <div class="card-icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="card-content">
                    <h3>Arrivées du mois</h3>
                    <div class="card-value">{{ count($arrivees) }}</div>
                </div>
            </div>
            
            <div class="summary-card departures-card">
                <div class="card-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div class="card-content">
                    <h3>Départs du mois</h3>
                    <div class="card-value">{{ count($departs) }}</div>
                </div>
            </div>         
        </div>
    </div>

    <!-- Onglets pour les arrivées et départs -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab-button active" data-tab="tab-arrivees">
                <i class="fas fa-sign-in-alt"></i> Arrivées
                <span class="tab-count">{{ count($arrivees) }}</span>
            </button>
            <button class="tab-button" data-tab="tab-departs">
                <i class="fas fa-sign-out-alt"></i> Départs
                <span class="tab-count">{{ count($departs) }}</span>
            </button>
        </div>
        
        <div class="tab-content">
            <!-- Onglet Arrivées -->
            <div class="tab-pane active" id="tab-arrivees">
                <div class="table-responsive">
                    @if(count($arrivees) > 0)
                        <table class="movements-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Résident</th>
                                    <th>Chambre</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Établissement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arrivees->sortBy('DATEINSCRIPTION') as $resident)
                                    <tr class="resident-row {{ Carbon\Carbon::parse($resident->DATEINSCRIPTION)->isToday() ? 'today' : '' }}">
                                        <td class="date-cell">
                                            <div class="date-display">
                                                <div class="date-day">{{ Carbon\Carbon::parse($resident->DATEINSCRIPTION)->format('d') }}</div>
                                                <div class="date-month">{{ Carbon\Carbon::parse($resident->DATEINSCRIPTION)->locale('fr')->shortMonthName }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT }}</td>
                                        <td class="chambre-cell">
                                            @if($resident->chambre)
                                                <span class="chambre-badge">{{ $resident->chambre->IDBATIMENT }}{{ str_pad($resident->chambre->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @elseif($resident->CHAMBREASSIGNE)
                                                <span class="chambre-badge">{{ $resident->chambreAssigne->IDBATIMENT }}{{ str_pad($resident->chambreAssigne->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @else
                                                <span class="no-room">Non assignée</span>
                                            @endif
                                        </td>
                                        <td class="tel-cell">
                                            @if($resident->TELRESIDENT)
                                                <a href="tel:{{ $resident->TELRESIDENT }}" class="tel-link">{{ $resident->TELRESIDENT }}</a>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td>{{ $resident->MAILRESIDENT }}</td>
                                        <td>{{ $resident->ETABLISSEMENT }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-data">
                            <div class="no-data-icon"><i class="fas fa-calendar-times"></i></div>
                            <p>Aucune arrivée prévue pour ce mois.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Onglet Départs -->
            <div class="tab-pane" id="tab-departs">
                <div class="table-responsive">
                    @if(count($departs) > 0)
                        <table class="movements-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Résident</th>
                                    <th>Chambre</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Établissement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departs->sortBy('DATEDEPART') as $resident)
                                    @php
                                        $departDate = Carbon\Carbon::parse($resident->DATEDEPART);
                                        $isImminent = $departDate->isFuture() && $departDate->diffInDays(now()) <= 3;
                                    @endphp
                                    <tr class="resident-row {{ $departDate->isToday() ? 'today' : '' }} {{ $departDate->isPast() ? 'past' : '' }} {{ $isImminent ? 'imminent' : '' }}">
                                        <td class="date-cell">
                                            <div class="date-display">
                                                <div class="date-day">{{ $departDate->format('d') }}</div>
                                                <div class="date-month">{{ $departDate->locale('fr')->shortMonthName }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT }}</td>
                                        <td class="chambre-cell">
                                            @if($resident->chambre)
                                                <span class="chambre-badge">{{ $resident->chambre->IDBATIMENT }}{{ str_pad($resident->chambre->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @elseif($resident->CHAMBREASSIGNE)
                                                <span class="chambre-badge">{{ $resident->chambreAssigne->IDBATIMENT }}{{ str_pad($resident->chambreAssigne->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @else
                                                <span class="no-room">Non assignée</span>
                                            @endif
                                        </td>
                                        <td class="tel-cell">
                                            @if($resident->TELRESIDENT)
                                                <a href="tel:{{ $resident->TELRESIDENT }}" class="tel-link">{{ $resident->TELRESIDENT }}</a>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td>{{ $resident->MAILRESIDENT }}</td>
                                        <td>{{ $resident->ETABLISSEMENT }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-data">
                            <div class="no-data-icon"><i class="fas fa-calendar-times"></i></div>
                            <p>Aucun départ prévu pour ce mois.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Désactiver tous les onglets
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Activer l'onglet cliqué
            const tabId = button.getAttribute('data-tab');
            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>
@endsection