
@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chambres-libre.css') }}">
    @php
        $batimentActuel = "";
    @endphp
    <div class="Titre">
        <h1>Chambres</h1>
    </div>
    <div class="filter-container">
        <form method="GET" action="{{ route('filterDepartingResidents') }}">
            <label for="month">Mois :</label>
            <select name="month" id="month" required>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ (request('month') == $i || (empty(request('month')) && $i == now()->month)) ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>

            <label for="year">Année :</label>
            <select name="year" id="year" required>
                @for ($i = now()->year; $i <= now()->year + 5; $i++)
                    <option value="{{ $i }}" {{ (request('year') == $i || (empty(request('year')) && $i == now()->year)) ? 'selected' : '' }}>
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
                
                @if($chambre->resident)
                    @php
                        $isLeaving = isset($departingResidents) && in_array($chambre->IDRESIDENT, $departingResidents);
                    @endphp
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
                            <p class="depart-info">Départ prévu: {{ \Carbon\Carbon::parse($chambre->resident->DATEDEPART)->translatedFormat('d F Y') }}</p>
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
                    </div>
                @endif
            @endforeach
            </div> 
        </div>
    @endif
@endsection