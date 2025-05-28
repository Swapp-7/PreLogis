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
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Arrivée</th>
                        <th>Archivage</th>
                        <th>Chambre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($residentA as $resident)
                        <tr onclick="window.location='{{ route('resident-archive', ['idResidentA' => $resident->IDRESIDENTARCHIVE]) }}'">
                            <td>{{ $resident->NOMRESIDENTARCHIVE }}</td>
                            <td>{{ $resident->PRENOMRESIDENTARCHIVE }}</td>
                            <td>{{ $resident->MAILRESIDENTARCHIVE }}</td>
                            <td>{{ $resident->TELRESIDENTARCHIVE }}</td>
                            <td>{{ \Carbon\Carbon::parse($resident->DATEINSCRIPTIONARCHIVE)->translatedFormat('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($resident->DATEARCHIVE)->translatedFormat('d M Y') }}</td>
                            <td>
                                @if($resident->chambre)
                                    {{ $resident->chambre->IDBATIMENT }}{{ $resident->chambre->NUMEROCHAMBRE }}
                                @else
                                    <span class="text-muted">Chambre supprimé</span>
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
</style>
