@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/archive.css') }}">
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
                            <td>{{ $resident->chambre->IDBATIMENT }}{{ $resident->chambre->NUMEROCHAMBRE }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

<style>

</style>
