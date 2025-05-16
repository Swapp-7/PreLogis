@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chambre.css') }}">
    <div class="placeholder">
        <h1>Chambres</h1>
    </div>
    <div class="card-container">
        @foreach ($chambres as $chambre)
            @if($chambre->resident)
                <div class="card" onclick="window.location='{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}'">
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
                </div>
            @else
                
                <div class="card" onclick="window.location='{{ route('resident', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}'">
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

@endsection
<style>
    
</style>