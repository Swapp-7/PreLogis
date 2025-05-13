<!-- Modal pour ajouter une Occupation -->
<div id="addEventModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h3>Ajouter une Occupation</h3>
        <form action="{{ route('nouvelleOccupation') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="moment">Moment</label>
                <select id="moment" name="moment" class="form-control" required>
                    @foreach($moments as $moment)
                        <option value="{{ $moment->IDMOMENT }}">{{ $moment->LIBELLEMOMENT }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="event">Événement</label>
                <select id="event" name="event" class="form-control" required>
                    @foreach($evenements as $evenement)
                        <option value="{{ $evenement->IDEVENEMENT }}">{{ $evenement->NOMEVENEMENT }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" id="salle" name="salle" value="{{ $salle->IDSALLE }}">
            <button type="submit" class="btn btn-success">Ajouter</button>
        </form>
    </div>
</div>
