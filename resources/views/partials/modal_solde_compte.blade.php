<div id="soldeCompteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Solde de Tout Compte - Saisie des Montants</h2>
            <span class="close" onclick="closeSoldeCompteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="soldeCompteForm" action="{{ route('resident.solde-tout-compte-pdf.post', ['idResident' => $resident->IDRESIDENT]) }}" method="POST" target="_blank">
                @csrf
                
                <div class="form-group">
                    <label for="redevance">Redevance jusqu'à la date de sortie (€)</label>
                    <input type="number" id="redevance" name="redevance" class="form-control" 
                           step="0.01" min="0" value="0" required>
                </div>

                <div class="form-group">
                    <label for="depot_garantie">Dépôt de garantie versé à l'entrée (€)</label>
                    <input type="number" id="depot_garantie" name="depot_garantie" class="form-control" 
                           step="0.01" min="0" value="505" required>
                </div>

                <div class="form-group">
                    <label for="deductions">Déductions (réparations, nettoyage, etc.) (€)</label>
                    <input type="number" id="deductions" name="deductions" class="form-control" 
                           step="0.01" min="0" value="0" required>
                </div>

                <div class="form-group">
                    <label for="solde_caf">Solde CAF (€)</label>
                    <input type="number" id="solde_caf" name="solde_caf" class="form-control" 
                           step="0.01" value="0" required>
                    <small class="form-text">Montant positif si CAF doit de l'argent, négatif si le résident doit à la CAF</small>
                </div>

                <!-- Résultats calculés automatiquement -->
                <div class="calculation-section">
                    <h3>Calculs automatiques</h3>
                    
                    <div class="form-group">
                        <label for="montant_du_au_resident_display" class="montant">Montant dû au résident (€)</label>
                        <input type="text" id="montant_du_au_resident_display" class="form-control calculation-result" readonly>
                        <input type="hidden" id="montant_du_au_resident" name="montant_du_au_resident">
                    </div>

                    <div class="form-group">
                        <label for="montant_du_par_resident_display" class="montant">Montant dû par le résident (€)</label>
                        <input type="text" id="montant_du_par_resident_display" class="form-control calculation-result" readonly>
                        <input type="hidden" id="montant_du_par_resident" name="montant_du_par_resident">
                    </div>
                </div>

                <div class="form-group">
                    <label for="date_reglement">Date de règlement</label>
                    <input type="date" id="date_reglement" name="date_reglement" class="form-control" 
                           value="{{ now()->format('Y-m-d') }}" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeSoldeCompteModal()">Annuler</button>
                    <button type="submit" class="btn-primary">Générer le PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: none;
    width: 90%;
    max-width: 600px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    color: #333;
}

.modal-header {
    background-color: #2c3e50;
    color: white;
    padding: 20px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 600;
}

.close {
    color: #FDC11F;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s;
}

.close:hover,
.close:focus {
    color: #fff;
    text-decoration: none;
}

.modal-body {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 300;
    color: #fefefe;
    font-size: 14px;
}

.montant {
    font-weight: bold;
    color: #20364B;
}

.form-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #FDC11F;
    box-shadow: 0 0 0 3px rgba(253, 193, 31, 0.1);
}

.calculation-section {
    background-color: rgba(255, 255, 255, 0.08);
    border: 2px solid #20364B;
    border-radius: 8px;
    padding: 20px;
    margin: 25px 0;
}

.calculation-section h3 {
    color: #FDC11F;
    font-size: 16px;
    margin: 0 0 15px 0;
    text-align: center;
    font-weight: bold;
}

.calculation-result {
    background-color: #e9ecef !important;
    font-weight: bold;
    color: #20364B !important;
    cursor: not-allowed;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-primary, .btn-secondary {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary {
    background-color: #FDC11F;
    color: #2c3e50;
}

.btn-primary:hover {
    background-color: #e6ae15;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>

<script>
function openSoldeCompteModal() {
    document.getElementById('soldeCompteModal').style.display = 'block';
    calculateAmounts(); // Calculer les montants à l'ouverture
}

function closeSoldeCompteModal() {
    document.getElementById('soldeCompteModal').style.display = 'none';
}

// Fermer la modal si on clique à l'extérieur
window.onclick = function(event) {
    var modal = document.getElementById('soldeCompteModal');
    if (event.target == modal) {
        closeSoldeCompteModal();
    }
}

// Fonction de calcul des montants
function calculateAmounts() {
    const redevance = parseFloat(document.getElementById('redevance').value) || 0;
    const depotGarantie = parseFloat(document.getElementById('depot_garantie').value) || 0;
    const deductions = parseFloat(document.getElementById('deductions').value) || 0;
    const soldeCaf = parseFloat(document.getElementById('solde_caf').value) || 0;
    
    // Calcul du montant net après déductions
    const montantNetDepot = depotGarantie - deductions;
    
    // Calcul du solde total (dépôt - redevance + solde CAF)
    const soldeTotal = montantNetDepot - redevance + soldeCaf;
    
    let montantDuAuResident = 0;
    let montantDuParResident = 0;
    
    if (soldeTotal >= 0) {
        // Le résident a un crédit
        montantDuAuResident = soldeTotal;
        montantDuParResident = 0;
    } else {
        // Le résident a une dette
        montantDuAuResident = 0;
        montantDuParResident = Math.abs(soldeTotal);
    }
    
    // Mise à jour des champs d'affichage
    document.getElementById('montant_du_au_resident_display').value = montantDuAuResident.toFixed(2) + ' €';
    document.getElementById('montant_du_par_resident_display').value = montantDuParResident.toFixed(2) + ' €';
    
    // Mise à jour des champs cachés pour l'envoi du formulaire
    document.getElementById('montant_du_au_resident').value = montantDuAuResident.toFixed(2);
    document.getElementById('montant_du_par_resident').value = montantDuParResident.toFixed(2);
}

// Ajouter les écouteurs d'événements pour le calcul automatique
document.addEventListener('DOMContentLoaded', function() {
    const fieldsToWatch = ['redevance', 'depot_garantie', 'deductions', 'solde_caf'];
    
    fieldsToWatch.forEach(function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', calculateAmounts);
            field.addEventListener('change', calculateAmounts);
        }
    });
    
    // Calcul initial
    calculateAmounts();
});
</script>
