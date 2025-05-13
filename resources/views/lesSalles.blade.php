@php use App\Helpers\ColorHelper; @endphp
@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/calendrier-salle.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>

@section('content')
<div class="page-container">
    <h1 class="page-title">Emploi du temps de la semaine du {{ $startDate->translatedFormat('d F') }} au {{ $endDate->translatedFormat('d F Y') }}</h1>
    
    <div class="calendar-nav" style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <a href="{{ route('lesSalles', ['weekOffset' => $weekOffset - 1]) }}" class="arrow-btn">← Semaine précédente</a>
        <span class="week-label">{{ $startDate->translatedFormat('d M') }} – {{ $endDate->translatedFormat('d M Y') }}</span>
        <a href="{{ route('lesSalles', ['weekOffset' => $weekOffset + 1]) }}" class="arrow-btn">Semaine suivante →</a>
    </div>

    <div class="day-schedule">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Jour</th>
                    <th>Moment</th>
                    @foreach($salles as $salle)
                        <th>{{ $salle->LIBELLESALLE }}</th>
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
    
    <button id="export-img" class="btn btn-primary mt-4">Exporter en image</button>
    <a href="{{ route('salle') }}" class="btn btn-primary mt-4">← Retour aux salles</a>
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
  });
</script>


