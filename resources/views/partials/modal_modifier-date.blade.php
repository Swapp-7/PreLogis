<!-- Modal pour modifier les dates -->
<div id="dateModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeDateModal()">&times;</span>
        <h2>Modifier les dates</h2>
        <p id="resident-name-display"></p>
        
        <form id="dateForm" method="POST" action="{{ route('updateFutureResidentDates') }}">
            @csrf
            <input type="hidden" id="resident_id" name="resident_id">
            
            <div class="form-group">
                <label for="date_arrivee">Date d'arrivée :</label>
                <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" required>
                <div class="help-text">
                    <i class="fas fa-info-circle"></i> La date d'arrivée doit être après le départ du résident actuel.
                </div>
            </div>
            
            <div class="form-group">
                <label for="date_depart">Date de départ :</label>
                <input type="date" class="form-control" id="date_depart" name="date_depart">
                <div class="help-text">
                    <i class="fas fa-info-circle"></i> Optionnel. La date de départ doit être après la date d'arrivée.
                </div>
            </div>
            
            <div class="date-constraints-info" id="date-constraints-info">
                <!-- Informations de contraintes dynamiques -->
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentResidentId = null;
    let currentResidentName = null;
    let originalDate = null;
    let originalDepartDate = null;
    let minDate = null;
    let maxDate = null;
    
    // S'assurer que le DOM est chargé avant d'ajouter les event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les event listeners
        initEventListeners();
    });
    
    function initEventListeners() {
        // Validation du formulaire
        document.getElementById('dateForm').addEventListener('submit', validateForm);
        
        // Validation de la date d'arrivée
        document.getElementById('date_arrivee').addEventListener('change', updateDepartMinDate);
        
        // Fermer le modal quand on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('dateModal');
            if (event.target == modal) {
                closeDateModal();
            }
        };
    }
    
    function openDateModal(residentId, residentName, dateInscription, dateDepart = null) {
        currentResidentId = residentId;
        currentResidentName = residentName;
        originalDate = dateInscription;
        originalDepartDate = dateDepart;
        
        // Remplir les valeurs du formulaire
        document.getElementById('resident_id').value = residentId;
        document.getElementById('resident-name-display').textContent = residentName;
        document.getElementById('date_arrivee').value = dateInscription.split(' ')[0]; // Format YYYY-MM-DD
        
        if (dateDepart) {
            document.getElementById('date_depart').value = dateDepart.split(' ')[0]; // Format YYYY-MM-DD
        } else {
            document.getElementById('date_depart').value = ''; // Aucune date de départ définie
        }
        
        // Récupérer tous les futurs résidents
        const futureResidents = {!! json_encode($futureResidents) !!};
        
        // Déterminer les contraintes de dates
        const residentActuel = {!! json_encode($resident ?? null) !!};
        let constraintMessage = "";
        
        // Trouver la position du résident actuel dans la liste des futurs résidents
        let residentIndex = -1;
        if (futureResidents && futureResidents.length > 0) {
            residentIndex = futureResidents.findIndex(fr => fr.IDRESIDENT == residentId);
        }
        
        // Déterminer la date minimale d'arrivée
        if (residentIndex === 0) {
            // Premier futur résident: minDate = date de départ du résident actuel + 1 jour
            if (residentActuel && residentActuel.DATEDEPART) {
                const dateDepartActuel = new Date(residentActuel.DATEDEPART);
                dateDepartActuel.setDate(dateDepartActuel.getDate() + 1);
                minDate = dateDepartActuel.toISOString().split('T')[0];
                constraintMessage = `• Date minimale d'arrivée: ${formatDateFr(minDate)} (après le départ du résident actuel)`;
            } else {
                minDate = new Date().toISOString().split('T')[0];
                constraintMessage = `• Date minimale d'arrivée: ${formatDateFr(minDate)} (aujourd'hui)`;
            }
        } else if (residentIndex > 0) {
            // Pour les futurs résidents suivants: minDate = date de départ du futur résident précédent + 1 jour
            // Si le futur résident précédent n'a pas de date de départ, utiliser sa date d'arrivée + durée minimum (ex: 7 jours)
            const previousResident = futureResidents[residentIndex - 1];
            
            if (previousResident.DATEDEPART) {
                const dateDepartPrecedent = new Date(previousResident.DATEDEPART);
                dateDepartPrecedent.setDate(dateDepartPrecedent.getDate() + 1);
                minDate = dateDepartPrecedent.toISOString().split('T')[0];
                constraintMessage = `• Date minimale d'arrivée: ${formatDateFr(minDate)} (après le départ de ${previousResident.NOMRESIDENT} ${previousResident.PRENOMRESIDENT})`;
            } else {
                // Si pas de date de départ, on estime un séjour minimum de 7 jours
                const dateArriveePrecedent = new Date(previousResident.DATEINSCRIPTION);
                dateArriveePrecedent.setDate(dateArriveePrecedent.getDate() + 7);
                minDate = dateArriveePrecedent.toISOString().split('T')[0];
                constraintMessage = `• Date minimale d'arrivée: ${formatDateFr(minDate)} (estimation après le séjour de ${previousResident.NOMRESIDENT} ${previousResident.PRENOMRESIDENT})`;
            }
        } else {
            // Résident non trouvé dans la liste ou aucun futur résident
            minDate = new Date().toISOString().split('T')[0];
            constraintMessage = `• Date minimale d'arrivée: ${formatDateFr(minDate)} (aujourd'hui)`;
        }
        
        document.getElementById('date_arrivee').min = minDate;
        
        // Déterminer la date maximale
        if (futureResidents && futureResidents.length > 0) {
            // Trouver le prochain futur résident après celui-ci
            if (residentIndex >= 0 && residentIndex < futureResidents.length - 1) {
                const nextResident = futureResidents[residentIndex + 1];
                const nextResidentDate = new Date(nextResident.DATEINSCRIPTION);
                nextResidentDate.setDate(nextResidentDate.getDate() - 1); // 1 jour avant
                maxDate = nextResidentDate.toISOString().split('T')[0];
                document.getElementById('date_arrivee').max = maxDate;
                
                constraintMessage += `<br>• Date maximale d'arrivée et de départ: ${formatDateFr(maxDate)} (avant l'arrivée de ${nextResident.NOMRESIDENT} ${nextResident.PRENOMRESIDENT})`;
            } else {
                // Dernier futur résident ou non trouvé dans la liste, pas de date maximale
                maxDate = null;
                document.getElementById('date_arrivee').removeAttribute('max');
                
                constraintMessage += "<br>• Pas de date maximale d'arrivée (dernier futur résident prévu)";
            }
        } else {
            // Pas d'autres futurs résidents, pas de date maximale
            maxDate = null;
            document.getElementById('date_arrivee').removeAttribute('max');
            
            constraintMessage += "<br>• Pas de date maximale d'arrivée (aucun autre futur résident prévu)";
        }
        
        // Ajouter des contraintes pour la date de départ
        constraintMessage += "<br>• La date de départ doit être après la date d'arrivée";
        
        // Contraintes supplémentaires pour la date de départ (si le résident n'est pas le dernier)
        if (residentIndex >= 0 && residentIndex < futureResidents.length - 1) {
            const nextResident = futureResidents[residentIndex + 1];
            constraintMessage += `<br>• La date de départ doit être avant l'arrivée de ${nextResident.NOMRESIDENT} ${nextResident.PRENOMRESIDENT}`;
        }
        
        // Afficher les contraintes
        document.getElementById('date-constraints-info').innerHTML = `
            <p><i class="fas fa-exclamation-triangle"></i> <strong>Contraintes:</strong></p>
            <p>${constraintMessage}</p>
        `;
        
        // Mettre à jour les contraintes sur la date de départ
        const dateDepartInput = document.getElementById('date_depart');
        // La date de départ doit toujours être après la date d'arrivée
        dateDepartInput.min = document.getElementById('date_arrivee').value;
        
        // Si ce n'est pas le dernier résident, la date de départ doit être avant l'arrivée du prochain
        if (maxDate) {
            dateDepartInput.max = maxDate;
        } else {
            dateDepartInput.removeAttribute('max');
        }
        
        // Afficher le modal
        document.getElementById('dateModal').style.display = 'block';
    }
    
    function closeDateModal() {
        document.getElementById('dateModal').style.display = 'none';
    }
    
    // Formatter une date en format français
    function formatDateFr(dateStr) {
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return new Date(dateStr).toLocaleDateString('fr-FR', options);
    }
    
    // Mise à jour de la date minimale de départ quand la date d'arrivée change
    function updateDepartMinDate() {
        const dateDepart = document.getElementById('date_depart');
        if (dateDepart.value) {
            const dateArriveeValue = new Date(this.value);
            const dateDepartValue = new Date(dateDepart.value);
            
            if (dateDepartValue <= dateArriveeValue) {
                // Réinitialiser la date de départ
                dateDepart.value = '';
            }
        }
        
        // Mettre à jour la date minimum du champ de départ
        dateDepart.min = this.value;
    }
    
    // Validation du formulaire
    function validateForm(e) {
        const dateArrivee = document.getElementById('date_arrivee');
        const dateArriveeValue = new Date(dateArrivee.value);
        const dateDepart = document.getElementById('date_depart');
        
        // Vérifier si la date d'arrivée est dans les limites
        if (minDate && dateArriveeValue < new Date(minDate)) {
            e.preventDefault();
            alert(`La date d'arrivée doit être après ${formatDateFr(minDate)}`);
            return false;
        }
        
        if (maxDate && dateArriveeValue > new Date(maxDate)) {
            e.preventDefault();
            alert(`La date d'arrivée doit être avant ${formatDateFr(maxDate)}`);
            return false;
        }
        
        // Vérifier si la date de départ est après la date d'arrivée
        if (dateDepart.value) {
            const dateDepartValue = new Date(dateDepart.value);
            
            // La date de départ doit être après la date d'arrivée
            if (dateDepartValue <= dateArriveeValue) {
                e.preventDefault();
                alert(`La date de départ doit être après la date d'arrivée`);
                return false;
            }
            
            // Récupérer les futurs résidents pour vérifier les contraintes
            const futureResidents = {!! json_encode($futureResidents) !!};
            let residentIndex = -1;
            
            if (futureResidents && futureResidents.length > 0) {
                residentIndex = futureResidents.findIndex(fr => fr.IDRESIDENT == currentResidentId);
            }
            
            // Si un prochain résident existe, vérifier que la date de départ est avant son arrivée
            if (residentIndex >= 0 && residentIndex < futureResidents.length - 1) {
                const nextResident = futureResidents[residentIndex + 1];
                const nextResidentDate = new Date(nextResident.DATEINSCRIPTION);
                
                // La date de départ doit être au moins 1 jour avant l'arrivée du prochain résident
                nextResidentDate.setDate(nextResidentDate.getDate() - 1);
                
                if (dateDepartValue > nextResidentDate) {
                    e.preventDefault();
                    alert(`La date de départ doit être avant l'arrivée du prochain résident (${formatDateFr(nextResidentDate.toISOString().split('T')[0])})`);
                    return false;
                }
            }
        }
        
        return true;
    }
</script>

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
        background-color: rgba(0, 0, 0, 0.7);
    }
    
    .modal-content {
        position: relative;
        background-color: #20364B;
        margin: 10% auto;
        padding: 25px;
        border: 1px solid rgba(253, 193, 31, 0.3);
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: modalFadeIn 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    
    .close-modal {
        color: #FDC11F;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .close-modal:hover {
        color: #e6ae15;
    }
    
    .modal h2 {
        color: #FDC11F;
        margin-top: 0;
        border-bottom: 1px dashed rgba(253, 193, 31, 0.5);
        padding-bottom: 10px;
    }
    
    #resident-name-display {
        margin-bottom: 20px;
        font-size: 1.1rem;
        font-weight: 500;
    }
    
    .date-constraints-info {
        background-color: rgba(255, 255, 255, 0.08);
        border-radius: 6px;
        padding: 12px;
        margin: 15px 0;
        border-left: 3px solid #FDC11F;
    }
    
    .date-constraints-info p {
        margin: 5px 0;
        font-size: 0.95rem;
    }
    
    .date-constraints-info strong {
        color: #FDC11F;
    }
    </style>