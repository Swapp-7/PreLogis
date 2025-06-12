@extends('layouts.app')
@section('title', 'Tableau de Bord')
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
            
            <a href="{{ route('groups.index') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-user-friends"></i></div>
                <h2>Groupes</h2>
                <p>Gérer les groupes de résidents</p>
            </a>
            
            <a href="{{ route('planning.resident') }}" class="grid-card">
                <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                <h2>Planning Résidents</h2>
                <p>Voir les arrivées et départs</p>
            </a>
        </div>

        
        
        <!-- Mouvements de la semaine -->
        <div class="movements-section">
            <div class="planning-header">
            <h2>Mouvements de la semaine ({{ \Carbon\Carbon::now()->startOfWeek()->format('d/m') }} - {{ \Carbon\Carbon::now()->endOfWeek()->format('d/m') }})</h2>
            <a href="{{ route('planning.resident') }}" class="view-all-link">Voir tous les mouvements <i class="fas fa-arrow-right"></i></a>
        </div>
        
        @php
            $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
            $endOfWeek = \Carbon\Carbon::now()->endOfWeek();
            
            // Récupérer les arrivées de la semaine
            $arrivees = \App\Models\Resident::with('parents')
            ->whereBetween('DATEINSCRIPTION', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('DATEINSCRIPTION')
            ->take(5)
            ->get();
            
            // Récupérer les départs de la semaine
            $departs = \App\Models\Resident::with('parents')
            ->whereBetween('DATEDEPART', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                                         ->orderBy('DATEDEPART')
                                         ->take(5)
                                         ->get();
                                         @endphp

<div class="movements-tabs">
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
                    @if(count($arrivees) > 0)
                    <table class="movements-table">
                        <thead>
                            <tr>
                                    <th>Date</th>
                                    <th>Résident</th>
                                    <th>Chambre</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Nom parent 1</th>
                                    <th>Tel parent 1</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arrivees as $resident)
                                    <tr class="resident-row {{ \Carbon\Carbon::parse($resident->DATEINSCRIPTION)->isToday() ? 'today' : '' }}">
                                        <td class="date-cell">
                                            <div class="date-display">
                                                <div class="date-day">{{ \Carbon\Carbon::parse($resident->DATEINSCRIPTION)->format('d') }}</div>
                                                <div class="date-month">{{ \Carbon\Carbon::parse($resident->DATEINSCRIPTION)->locale('fr')->shortMonthName }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT }}</td>
                                        <td class="chambre-cell">
                                            @if($resident->chambre)
                                                <span class="chambre-badge">{{ $resident->chambre->IDBATIMENT }}{{ str_pad($resident->chambre->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @else
                                            <span class="chambre-badge">{{ $resident->chambreAssigne->IDBATIMENT }}{{ str_pad($resident->chambreAssigne->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @endif
                                        </td>
                                        <td class="tel-cell">
                                            @if($resident->TELRESIDENT)
                                                <span class="tel-display">{{ $resident->TELRESIDENT }}</span>
                                                @else
                                                <span class="no-data">-</span>
                                            @endif
                                        </td>
                                        <td class="email-cell">
                                            @if($resident->MAILRESIDENT)
                                            <span class="email-display">{{ $resident->MAILRESIDENT }}</span>
                                            @else
                                            <span class="no-data">-</span>
                                            @endif
                                        </td>
                                        <td class="parent-cell">
                                            @if($resident->parents && $resident->parents->first())
                                                {{ $resident->parents->first()->NOMPARENT }} {{ $resident->parents->first()->PRENOMPARENT }}
                                            @else
                                                <span class="no-data">-</span>
                                                @endif
                                            </td>
                                            <td class="parent-tel-cell">
                                                @if($resident->parents && $resident->parents->first() && $resident->parents->first()->TELPARENT)
                                                    {{ $resident->parents->first()->TELPARENT }}
                                            @else
                                                <span class="no-data">-</span>
                                                @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                    <div class="no-data">
                        <p>Aucune arrivée prévue cette semaine.</p>
                    </div>
                    @endif
                </div>
                
                <!-- Onglet Départs -->
                <div class="tab-pane" id="tab-departs">
                    @if(count($departs) > 0)
                        <table class="movements-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Résident</th>
                                    <th>Chambre</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th>Nom parent 1</th>
                                    <th>Tel parent 1</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departs as $resident)
                                <tr class="resident-row {{ \Carbon\Carbon::parse($resident->DATEDEPART)->isToday() ? 'today' : '' }}">
                                        <td class="date-cell">
                                            <div class="date-display">
                                                <div class="date-day">{{ \Carbon\Carbon::parse($resident->DATEDEPART)->format('d') }}</div>
                                                <div class="date-month">{{ \Carbon\Carbon::parse($resident->DATEDEPART)->locale('fr')->shortMonthName }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT }}</td>
                                        <td class="chambre-cell">
                                            @if($resident->chambre)
                                                <span class="chambre-badge">{{ $resident->chambre->IDBATIMENT }}{{ str_pad($resident->chambre->NUMEROCHAMBRE, 2, '0', STR_PAD_LEFT) }}</span>
                                            @else
                                                <span class="no-room">Non assignée</span>
                                                @endif
                                            </td>
                                            <td class="tel-cell">
                                                @if($resident->TELRESIDENT)
                                                <span class="tel-display">{{ $resident->TELRESIDENT }}</span>
                                                @else
                                                <span class="no-data">-</span>
                                            @endif
                                        </td>
                                        <td class="email-cell">
                                            @if($resident->MAILRESIDENT)
                                            <span class="email-display">{{ $resident->MAILRESIDENT }}</span>
                                            @else
                                            <span class="no-data">-</span>
                                            @endif
                                        </td>
                                        <td class="parent-cell">
                                            @if($resident->parents && $resident->parents->first())
                                            {{ $resident->parents->first()->NOMPARENT }} {{ $resident->parents->first()->PRENOMPARENT }}
                                            @else
                                                <span class="no-data">-</span>
                                                @endif
                                            </td>
                                        <td class="parent-tel-cell">
                                            @if($resident->parents && $resident->parents->first() && $resident->parents->first()->TELPARENT)
                                            {{ $resident->parents->first()->TELPARENT }}
                                            @else
                                                <span class="no-data">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                        </table>
                    @else
                    <div class="no-data">
                        <p>Aucun départ prévu cette semaine.</p>
                    </div>
                    @endif
                </div>
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
            const tabGroup = this.closest('.tabs').querySelectorAll('.tab-button');
            const contentGroup = this.closest('.movements-tabs').querySelectorAll('.tab-pane');
            
            tabGroup.forEach(btn => btn.classList.remove('active'));
            contentGroup.forEach(pane => pane.classList.remove('active'));
            
            // Activer l'onglet cliqué
            const tabId = button.getAttribute('data-tab');
            button.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<style>

</style>
@endsection