@php use App\Helpers\ColorHelper; @endphp
@extends('layouts.app')
@section('title', 'Planning des Salles')
<link rel="stylesheet" href="{{ asset('css/calendrier-salle.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>

@section('content')
<div class="page-container">
    <h1 class="page-title">Emploi du temps de la semaine du {{ $startDate->translatedFormat('d F') }} au {{ $endDate->translatedFormat('d F Y') }}</h1>
    
    <div class="calendar-nav" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <a href="{{ route('lesSalles', ['weekOffset' => $weekOffset - 1]) }}" class="arrow-btn">← Semaine précédente</a>
        <div class="week-selector-group">
            <span class="week-label">{{ $startDate->translatedFormat('d') }}-{{ $endDate->translatedFormat('d F Y') }}</span>
            <form id="weekSelectorForm" method="GET" action="{{ route('lesSalles') }}" style="display:inline;">
                <input type="date" name="date" id="weekSelector" value="{{ $startDate->toDateString() }}">
            </form>
        </div>
        <a href="{{ route('lesSalles', ['weekOffset' => $weekOffset + 1]) }}" class="arrow-btn">Semaine suivante →</a>
    </div>

    <div class="day-schedule">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Jour</th>
                    <th>Moment</th>
                    @foreach($salles as $salle)
                        <th>
                            <a href="{{ route('getSalle', ['IdSalle' => $salle->IDSALLE, 'date' => $startDate->toDateString()]) }}" 
                               class="salle-link" 
                               title="Voir le détail de {{ $salle->LIBELLESALLE }}">
                                {{ $salle->LIBELLESALLE }}
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($dates as $date)
                    @php
                        $count = 0;
                    @endphp
                    @foreach($moments as $momentIndex => $moment)
                        <tr class="{{ $momentIndex === 0 ? 'day-separator' : '' }}">
                            <td class="date-cell">
                                @if ($count == 1)
                                    {{ \Carbon\Carbon::parse($date->DATEPLANNING)->translatedFormat('l d/m') }}
                                
                                @endif
                                                                    
                            </td>
                            <td class="moment-cell cell-calendar">
                                {{ $moment->LIBELLEMOMENT }}
                            </td>
                            @foreach($salles as $salle)
                                @php
                                    $key = $salle->IDSALLE . '_' . \Carbon\Carbon::parse($date->DATEPLANNING)->format('Y-m-d');
                                    $occupation = $occupations[$key]->firstWhere('IDMOMENT', $moment->IDMOMENT) ?? null;
                                @endphp
                                <td class="occupation-cell cell-calendar"
                                    style="background-color: {{ $occupation->evenement->COULEUR ?? '#FFFFFF' }}; 
                                        color: {{ \App\Helpers\ColorHelper::getTextColor($occupation->evenement->COULEUR ?? '#FFFFFF') }};">
                                    {{ $occupation->evenement->NOMEVENEMENT ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                        @php
                            $count++;
                        @endphp
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="export-section" style="margin-top: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <button id="export-img" class="btn btn-primary">Exporter en image</button>
        
        
        <a href="{{ route('salle') }}" class="btn btn-primary">← Retour aux salles</a>
    </div>
    <div class="excel-export-container">
        <div class="export-card">
            <div class="export-header">
                <i class="fas fa-file-excel"></i>
                <h3>Export Planning Annuel</h3>
            </div>
            <form method="GET" action="{{ route('occupations.export') }}" class="export-form">
                <div class="form-group">
                    <label for="export-year">Année :</label>
                    <select name="year" id="export-year" class="form-select">
                        @for($i = now()->year - 1; $i <= now()->year + 5; $i++)
                            <option value="{{ $i }}" {{ $i == now()->year ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn-export">
                    <i class="fas fa-download"></i>
                    <span>Télécharger Excel</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const exportBtn = document.getElementById('export-img');

    
    const startDate = @json($startDate->translatedFormat('d F Y'));
    const endDate = @json($endDate->translatedFormat('d F Y'));

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
        const tableElement = document.querySelector('.schedule-table');

        if (tableElement) {
            html2canvas(tableElement, {
                scale: 3,
                useCORS: true
            }).then(function(canvas) {
                const imgData = canvas.toDataURL('image/png');

                const fileName = `calendrier_${startDate.replace(/\s/g, '_')}_au_${endDate.replace(/\s/g, '_')}.png`;

                const link = document.createElement('a');
                link.href = imgData;
                link.download = fileName; 
                link.click();
            });
        } else {
            console.error('Le tableau n\'a pas été trouvé.');
        }
        });
    } else {
        console.error('Le bouton d\'exportation n\'a pas été trouvé.');
    }

    // Gestionnaire pour le sélecteur de semaine
    const weekSelector = document.getElementById('weekSelector');
    if (weekSelector) {
        weekSelector.addEventListener('change', function() {
            document.getElementById('weekSelectorForm').submit();
        });
    }
  });
</script>


