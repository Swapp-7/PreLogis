@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/tableau-de-bord.css') }}">
<div class="dashboard-container">
    <h1 class="dashboard-title">Tableau de Bord</h1>
    
    <div class="dashboard-stats">
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-bed"></i></div>
            <div class="stat-info">
                <h3>Chambres</h3>
                <p class="stat-value">{{ App\Models\Chambre::count() }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3>Résidents</h3>
                <p class="stat-value">{{ App\Models\Resident::count() }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-archive"></i></div>
            <div class="stat-info">
                <h3>Residents archivées</h3>
                <p class="stat-value">{{ App\Models\ResidentArchive::count() }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info">
                <h3>Occupations aujourd'hui</h3>
                <p class="stat-value">{{ App\Models\Occupation::where('DATEPLANNING', \Carbon\Carbon::today()->format('Y-m-d'))->where('ESTOCCUPEE', true)->count() }}</p>
            </div>
        </div>
    </div>
    
    <div class="planning-preview">
        <div class="planning-header">
            <h2>Planning des salles - {{ \Carbon\Carbon::now()->translatedFormat('l d F Y') }}</h2>
            <a href="{{ route('lesSalles') }}" class="view-all-link">Voir le planning complet <i class="fas fa-arrow-right"></i></a>
        </div>
        
        @php
            $today = \Carbon\Carbon::today()->format('Y-m-d');
            $todayDate = \App\Models\Dates::where('DATEPLANNING', $today)->first();
            $salles = \App\Models\Salle::take(5)->get();
            $moments = \App\Models\MomentEvenement::all();
            
            // Récupérer les occupations pour aujourd'hui
            $occupationsToday = [];
            if ($todayDate) {
                foreach($salles as $salle) {
                    $key = $salle->IDSALLE . '_' . $today;
                    $occupationsToday[$key] = \App\Models\Occupation::where('DATEPLANNING', $today)
                        ->where('IDSALLE', $salle->IDSALLE)
                        ->where('ESTOCCUPEE', true)
                        ->get();
                }
            }
        @endphp
        
        @if($todayDate)
        <div class="mini-planning-table">
                <table>
                    <thead>
                        <tr>
                            <th>Moment</th>
                            @foreach($salles as $salle)
                            <th>{{ $salle->LIBELLESALLE }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moments as $moment)
                            <tr>
                                <td class="moment-cell">{{ $moment->LIBELLEMOMENT }}</td>
                                @foreach($salles as $salle)
                                @php
                                        $key = $salle->IDSALLE . '_' . $today;
                                        $occupation = isset($occupationsToday[$key]) ? 
                                            $occupationsToday[$key]->firstWhere('IDMOMENT', $moment->IDMOMENT) : null;
                                    @endphp
                                    <td class="occupation-cell"
                                        style="background-color: {{ $occupation && $occupation->evenement ? $occupation->evenement->COULEUR : '#FFFFFF' }}; 
                                               color: {{ $occupation && $occupation->evenement ? 
                                                   \App\Helpers\ColorHelper::getTextColor($occupation->evenement->COULEUR) : '#000000' }};">
                                        {{ $occupation && $occupation->evenement ? $occupation->evenement->NOMEVENEMENT : '-' }}
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
        @else
            <div class="no-planning-data">
                <p>Aucune donnée de planning disponible pour aujourd'hui.</p>
                <a href="{{ route('lesSalles') }}" class="btn btn-accent">Voir le planning complet</a>
            </div>
            @endif
        </div>

        <div class="dashboard-grid">
            <a href="{{ route('batiment') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-building"></i></div>
                <h2>Bâtiments</h2>
                <p>Gérer les bâtiments et accéder aux chambres</p>
            </a>
            
            <a href="{{ route('chambreLibre') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-bed"></i></div>
                <h2>Chambres Libres</h2>
                <p>Voir les chambres disponibles</p>
            </a>
            
            <a href="{{ route('salle') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-door-open"></i></div>
                <h2>Salles</h2>
                <p>Gérer les salles et leurs occupations</p>
            </a>
            
            <a href="{{ route('lesSalles') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                <h2>Planning Salles</h2>
                <p>Consulter l'emploi du temps des salles</p>
            </a>
        </div>

        <div class="dashboard-grid residents-grid">
            <a href="{{ route('allResident') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-users"></i></div>
                <h2>Résidents</h2>
                <p>Gérer tous les résidents</p>
            </a>
            
            <a href="{{ route('archive') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-archive"></i></div>
                <h2>Archives</h2>
                <p>Consulter les résidents archivés</p>
            </a>
        </div>
    
    <!-- Aperçu du planning des salles d'aujourd'hui -->
    
</div>
@endsection

<style>

</style>