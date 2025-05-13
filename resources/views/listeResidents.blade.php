@extends('layouts.app')

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

        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Chambre</th>

                </tr>
            </thead>
            <tbody>
            @foreach($residents as $resident)
                @php $chambre = $resident->chambre; @endphp
                <tr @if($chambre) onclick="window.location='{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}'" @endif>
                    <td>{{ $resident->NOMRESIDENT }}</td>
                    <td>{{ $resident->PRENOMRESIDENT }}</td>
                    <td>{{ $resident->MAILRESIDENT }}</td>
                    <td>{{ $resident->TELRESIDENT }}</td>
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