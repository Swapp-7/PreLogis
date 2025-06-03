@extends('layouts.app')

@section('title', 'Archives des Résidents')

@section('content')
<link rel="stylesheet" href="{{ asset('css/liste-residents.css') }}">
    <div class="container">
        <h1>Résidents Archivés</h1>

        <!-- Barre de recherche -->
        <form method="GET" action="{{ route('archive') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" value="{{ old('query', $query) }}"
                    placeholder="Rechercher un résident archivé..." />
                <button type="submit">Rechercher</button>
            </div>
        </form>

        <a href="{{ route('archives.export', ['query' => request('query')]) }}" class="export-btn">
            <i class="fas fa-file-excel"></i>
            <span>Exporter en Excel</span>
        </a>

        @if($residentA->isEmpty())
            <p class="text-center text-warning">Aucun résident archivé trouvé.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Arrivée</th>
                        <th>Archivage</th>
                        <th>Chambre(s)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($residentA as $resident)
                        <tr onclick="window.location='{{ route('resident-archive', ['idResidentA' => $resident->IDRESIDENTARCHIVE]) }}'">
                            <td>
                                @if($resident->isGroup())
                                    <span class="archive-type group-type">
                                        <i class="fas fa-users"></i> Groupe
                                    </span>
                                @else
                                    <span class="archive-type individual-type">
                                        <i class="fas fa-user"></i> Individuel
                                    </span>
                                @endif
                            </td>
                            <td>{{ $resident->NOMRESIDENTARCHIVE }}</td>
                            <td>
                                @if($resident->isGroup())
                                    <span class="text-muted">-</span>
                                @else
                                    {{ $resident->PRENOMRESIDENTARCHIVE }}
                                @endif
                            </td>
                            <td>{{ $resident->MAILRESIDENTARCHIVE }}</td>
                            <td>{{ $resident->TELRESIDENTARCHIVE }}</td>
                            <td>{{ \Carbon\Carbon::parse($resident->DATEINSCRIPTIONARCHIVE)->translatedFormat('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($resident->DATEARCHIVE)->translatedFormat('d M Y') }}</td>
                            <td>
                                @if($resident->isGroup())
                                    <div class="chambres-list">
                                        @php
                                            $chambres = $resident->getChambresOccupees();
                                        @endphp
                                        @if(count($chambres) > 0)
                                            @foreach($chambres as $chambre)
                                                <span class="chambre-badge">{{ $chambre }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Aucune chambre</span>
                                        @endif
                                    </div>
                                @else
                                    @if($resident->chambre)
                                        <span class="chambre-badge">{{ $resident->chambre->IDBATIMENT }}{{ $resident->chambre->NUMEROCHAMBRE }}</span>
                                    @else
                                        <span class="text-muted">Chambre supprimée</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

<style>
.export-btn {
    display: inline-flex;
    align-items: center;
    background-color: #4caf50;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
    margin-bottom: 20px;
    transition: background-color 0.3s;
    border: none;
    cursor: pointer;
}

.export-btn i {
    margin-right: 8px;
}

.export-btn:hover {
    background-color: #45a049;
    color: white;
}

/* Styles pour les types d'archives */
.archive-type {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.group-type {
    background-color: #FDC11F;
    color: #20364B;
}

.individual-type {
    background-color: #20364B;
    color: #fff;
    border: 1px solid #FDC11F;
}

.archive-type i {
    margin-right: 4px;
    font-size: 0.8rem;
}

/* Styles pour les chambres */
.chambres-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    max-width: 200px;
}

.chambre-badge {
    display: inline-block;
    background-color: rgba(253, 193, 31, 0.2);
    color: #FDC11F;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 500;
    border: 1px solid rgba(253, 193, 31, 0.5);
    white-space: nowrap;
}

/* Amélioration de la table pour les groupes */
.table tbody tr:hover {
    background-color: rgba(253, 193, 31, 0.1);
    cursor: pointer;
}

.table th:first-child,
.table td:first-child {
    width: 100px;
    text-align: center;
}

.table th:last-child,
.table td:last-child {
    max-width: 200px;
}

.text-muted {
    color: rgba(255, 255, 255, 0.6) !important;
    font-style: italic;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chambres-list {
        max-width: 150px;
    }
    
    .archive-type {
        font-size: 0.75rem;
        padding: 3px 6px;
    }
    
    .chambre-badge {
        font-size: 0.7rem;
        padding: 1px 4px;
    }
}
</style>
