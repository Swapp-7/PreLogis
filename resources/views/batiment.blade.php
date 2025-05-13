@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/batiment.css') }}">
    <div class="batiments">
        <h1>Bâtiments</h1>
        <div class="card-container">
            @foreach ($batiments as $batiment)
                @if ($batiment->CAPACITE != 0)
                <div class="card" onclick="window.location='{{ route('chambre', ['IdBatiment' => $batiment->IDBATIMENT]) }}'">
                    <h2>{{ $batiment->IDBATIMENT }}</h2>
                    <h3>Capacité: {{ $batiment->CAPACITE }}</h3>
                </div>
                @endif
            @endforeach
        </div>
    </div>
@endsection

<style>

</style>