<div id="departModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Planifier le départ</h2>
            <span class="close" onclick="closeDepartModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="departForm" action="{{ route('planifierDepart') }}" method="POST">
                @csrf
                @if(isset($resident) && $resident)
                <input type="hidden" name="idResident" value="{{ $resident->IDRESIDENT }}">
                
                <div class="form-group">
                    <label for="dateDepart">Date de départ prévue</label>
                    <input type="date" id="dateDepart" name="DATEDEPART" class="form-control" 
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    <small class="form-text">À cette date, le résident sera automatiquement archivé et la chambre sera libérée.</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeDepartModal()">Annuler</button>
                    <button type="submit" class="btn-primary">Confirmer</button>
                </div>
                @else
                <p>Aucun résident associé à cette chambre.</p>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeDepartModal()">Fermer</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<style>
/* Styles pour le modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    transition: all 0.3s ease;
}

.modal-content {
    background-color: var(--bg-dark, #20364B);
    margin: 10% auto;
    padding: 0;
    border: 1px solid var(--accent, #FDC11F);
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: var(--accent, #FDC11F);
    border-radius: 10px 10px 0 0;
}

.modal-header h2 {
    color: var(--bg-dark, #20364B);
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.close {
    color: var(--bg-dark, #20364B);
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #555;
}

.modal-body {
    padding: 20px;
    color: var(--white, #FFFFFF);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--white, #FFFFFF);
    font-size: 1rem;
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 0.85rem;
    color: var(--grey-light, #CDCBCE);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
}

.btn-primary, .btn-secondary {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background-color: var(--accent, #FDC11F);
    color: var(--bg-dark, #20364B);
}

.btn-primary:hover {
    background-color: #e6ae15;
}

.btn-secondary {
    background-color: rgba(255, 255, 255, 0.1);
    color: var(--white, #FFFFFF);
}

.btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.2);
}
</style>

<script>
function openDepartModal() {
    document.getElementById('departModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Empêche le défilement du contenu derrière
}

function closeDepartModal() {
    document.getElementById('departModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Restaure le défilement
}

// Fermer le modal si on clique en dehors
window.onclick = function(event) {
    const modal = document.getElementById('departModal');
    if (event.target == modal) {
        closeDepartModal();
    }
}
</script>