@php use App\Helpers\ColorHelper; @endphp
<link rel="stylesheet" href="{{ asset('css/detail-salle.css') }}">
<script src="{{ asset('js/detail-salle.js') }}"></script>


@extends('layouts.app')
@section('title', 'Détails de la Salle')
@section('content')
<div class="page-container">
    <h1 class="page-title">Détails de la Salle</h1>
    
    <div class="salle-details">
        <h2 class="salle-name">{{ $salle->LIBELLESALLE }}</h2>

        <div class="calendar">
            @php
                $dates = $occupations->pluck('DATEPLANNING')->unique();
            @endphp

            <!-- Navigation fléchée -->
            <div class="calendar-nav">
                <a class="arrow-btn" href="{{ route('getSalle', ['IdSalle' => $salle->IDSALLE, 'weekOffset' => $weekOffset - 1]) }}">
                    &#x25C0; Semaine précédente
                </a>
                <div class="week-selector-group">
                    <span class="week-label">{{ \Carbon\Carbon::parse($dates->first())->translatedFormat('d')}}-{{ \Carbon\Carbon::parse($dates->last())->translatedFormat('d F Y') }}</span>
                    <form id="weekSelectorForm" method="GET" action="{{ route('getSalle', ['IdSalle' => $salle->IDSALLE]) }}" style="display:inline;">
                        <input type="date" name="date" id="weekSelector" value="{{ \Carbon\Carbon::parse($dates->first())->toDateString() }}">
                    </form>
                </div>
                <a class="arrow-btn" href="{{ route('getSalle', ['IdSalle' => $salle->IDSALLE, 'weekOffset' => $weekOffset + 1]) }}">
                    Semaine suivante &#x25B6;
                </a>
            </div>

            <!-- Grille du calendrier -->
            <div class="calendar-grid">
                <div class="calendar-header">
                    <div class="calendar-corner"></div>
                    @foreach($dates as $date)
                        <div class="calendar-date">{{ \Carbon\Carbon::parse($date)->translatedFormat(' D d M') }}</div>
                    @endforeach
                </div>

                <div class="calendar-body">
                    @foreach($moments as $moment)
                        <div class="calendar-row">
                            <div class="calendar-moment">{{ $moment->LIBELLEMOMENT }}</div>
                            @foreach($dates as $date)
                            <div class="calendar-cell">
                                @php $found = false; @endphp
                                @foreach($occupations as $occupation)
                                    @if($occupation->evenement && $occupation->DATEPLANNING == $date && $occupation->IDMOMENT == $moment->IDMOMENT)
                                        <div 
                                            class="event-wrapper cell-clickable"
                                            data-date="{{ $date }}"
                                            data-moment="{{ $moment->IDMOMENT }}"
                                            data-salle="{{ $salle->IDSALLE }}"
                                            data-nom="{{ $occupation->evenement->NOMEVENEMENT }}"
                                            data-mail="{{ $occupation->evenement->MAILGROUPE }}"
                                            data-tel="{{ $occupation->evenement->TELGROUPE }}"
                                            data-referent="{{ $occupation->evenement->REFERENTGROUPE }}"
                                            title="Supprimer cette occupation"
                                            style="
                                                background-color: {{ $occupation->evenement->COULEUR ?? '#FFFFFF' }};
                                                color: {{ \App\Helpers\ColorHelper::getTextColor($occupation->evenement->COULEUR ?? '#FFFFFF') }};
                                            "
                                        >
                                            <span class="event-badge">{{ $occupation->evenement->NOMEVENEMENT }}</span>
                                        </div>
                                        @php $found = true; @endphp
                                    @endif
                                @endforeach
                                @if (!$found)
                                    <div 
                                        class="cell-clickable"
                                        data-date="{{ $date }}"
                                        data-moment="{{ $moment->IDMOMENT }}"
                                        data-salle="{{ $salle->IDSALLE }}"
                                        title="Ajouter une occupation"
                                    ></div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bouton pour valider les sélections -->

    <!-- Champ caché dans ton formulaire de la modale -->
    <form method="POST" action="{{ route('gererOccupation') }}" onsubmit="return prepareSubmit()">
        @csrf
        <input type="hidden" id="multi-occupations-data" name="multi_occupations">
        <input type="hidden" id="occupation-action" name="action" value="add">

        <label for="eventSelect">Choisir un groupe :</label>
        <select name="event" id="eventSelect" required>
            @foreach($evenements as $evenement)
                <option value="{{ $evenement->IDEVENEMENT }}">{{ $evenement->NOMEVENEMENT }}</option>
            @endforeach
        </select>

        <div style="margin-top: 10px;">
            <button type="submit" onclick="setAction('add')">Ajouter</button>
            <button type="submit" onclick="setAction('delete')">Supprimer</button>
        </div>
    </form>

    <div class="actions-row">
        <a href="{{ url('/Salle') }}" class="btn back-btn">← Retour</a>
        <button type="button" class="btn create-btn" id="addEventButton">Ajouter un groupe</button>
    </div>

    @include('partials.modal_occupation')
    @include('partials.modal_evenement')
    @include('partials.modal_info-groupe')

</div>


@endsection

