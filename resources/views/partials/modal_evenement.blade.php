<!-- Modal de création d'événement amélioré -->
<div id="createEventModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeCreateModal">&times;</span>
        <div class="modal-header">
            <h2>Créer un Nouveau Groupe</h2>
            <div class="modal-subtitle">Remplissez les informations du groupe</div>
        </div>
        <div class="modal-body">
            <form action="{{ route('nouvelEvenement') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nomEvenement">Nom du groupe</label>
                    <input type="text" id="nomEvenement" name="nomEvenement" value="{{ old('nomEvenement') }}" required>
                </div>
                <div class="form-group">
                    <label for="mailGroupe">Email du Groupe</label>
                    <input type="email" id="mailGroupe" name="mailGroupe" value="{{ old('mailGroupe') }}" required>
                </div>
                <div class="form-group">
                    <label for="telGroupe">Téléphone du Groupe</label>
                    <input type="tel" id="telGroupe" name="telGroupe" value="{{ old('telGroupe') }}" required>
                </div>
                <div class="form-group">
                    <label for="referentGroupe">Référent du Groupe</label>
                    <input type="text" id="referentGroupe" name="referentGroupe" value="{{ old('referentGroupe') }}" required>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-secondary" id="closeCreateBtn">Annuler</button>
                    <button type="submit" class="btn-modal btn-primary">Créer</button>
                </div>
            </form>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>