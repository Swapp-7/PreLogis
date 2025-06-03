@extends('layouts.app')

@section('title', 'Liste des Résidents')

@section('content')
<link rel="stylesheet" href="{{ asset('css/liste-residents.css') }}">
    <div class="container">
        <h1>Liste des Résidents</h1>
        
        <!-- Barre de recherche -->
        <form method="GET" action="{{ route('residents.search') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="query" class="form-control" placeholder="Rechercher un résident..." value="{{ request('query') }}">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </form>

        <a href="{{ route('residents.export', ['query' => request('query')]) }}" class="export-btn">
            <i class="fas fa-file-excel"></i>
            <span>Exporter en Excel</span>
        </a>

        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Nom parent 1</th>
                    <th>Tel parent 1</th>
                    <th>Chambre</th>
                </tr>
            </thead>
            <tbody>
            @foreach($residents as $resident)
                @php $chambre = $resident->chambre; @endphp
                <tr onclick="window.location='{{ $resident->isGroup() ? route('groups.show', ['id' => $resident->IDRESIDENT]) : route('getResident', ['IdResident' => $resident->IDRESIDENT]) }}'" >
                    <td>
                        @if($resident->TYPE == 'group')
                            <span class="badge badge-primary"><i class="fas fa-users"></i> Groupe</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-user"></i> Individuel</span>
                        @endif
                    </td>
                    <td>{{ $resident->NOMRESIDENT }}</td>
                    <td>{{ $resident->TYPE == 'group' ? '' : $resident->PRENOMRESIDENT }}</td>
                    <td>{{ $resident->MAILRESIDENT }}</td>
                    <td>{{ $resident->TELRESIDENT }}</td>
                    @if ($resident->TYPE == 'group' || $resident->parents->isEmpty())
                        <td><em>Non renseigné</em></td>
                        <td><em>Non renseigné</em></td>
                    @else
                        <td>{{ $resident->parents->first()->NOMPARENT }}</td>
                        <td>{{ $resident->parents->first()->TELPARENT }}</td>
                    @endif
                    
                    <td>
                        @if($chambre)
                            {{ $chambre->IDBATIMENT }}{{ $chambre->NUMEROCHAMBRE }}
                        @else
                            <em>Non assigné</em>
                        @endif
                    </td>
                </tr>
@endforeach
            </tbody>
        </table>
    </div>
@endsection

<style>
   
</style>