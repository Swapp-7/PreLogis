let selectedCells = [];

document.querySelectorAll('.cell-clickable').forEach(cell => {
    cell.addEventListener('click', function () {
        this.classList.toggle('selected');

        const cellData = {
            date: this.dataset.date,
            moment: this.dataset.moment,
            salle: this.dataset.salle
        };

        const index = selectedCells.findIndex(c =>
            c.date === cellData.date && c.moment === cellData.moment && c.salle === cellData.salle
        );

        if (index > -1) {
            selectedCells.splice(index, 1);
        } else {
            selectedCells.push(cellData);
        }
    });
});


let currentAction = 'add';

function setAction(action) {
    currentAction = action;
}

function prepareSubmit() {
    if (selectedCells.length === 0) {
        alert("Veuillez sélectionner au moins une cellule.");
        return false;
    }

    document.getElementById('multi-occupations-data').value = JSON.stringify(selectedCells);
    document.getElementById('occupation-action').value = currentAction;

    // rendre l'event obligatoire uniquement si on ajoute
    const eventSelect = document.getElementById('eventSelect');
    if (currentAction === 'add') {
        eventSelect.required = true;
    } else {
        eventSelect.required = false;
    }

    return true;
}

// Initialisation et gestion des modals
document.addEventListener('DOMContentLoaded', function() {
// Masquer tous les modals au chargement
const modals = document.querySelectorAll('.modal');
modals.forEach(modal => {
    modal.style.display = 'none';
});

// Fonction pour ouvrir un modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex'; // Utiliser flex pour le centrage
    }
}

// Fonction pour fermer un modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Fermeture du modal de création d'événement
document.getElementById('closeCreateModal').onclick = function() {
    closeModal('createEventModal');
};

// Bouton supplémentaire dans le footer pour fermer
if (document.getElementById('closeCreateBtn')) {
    document.getElementById('closeCreateBtn').onclick = function() {
        closeModal('createEventModal');
    };
}

// Ouverture du modal de création d'événement
document.getElementById('addEventButton').onclick = function() {
    openModal('createEventModal');
};

// Fermeture du modal d'info événement
document.getElementById('closeEventInfoModal').onclick = function() {
    closeModal('eventInfoModal');
};

// Bouton supplémentaire dans le footer pour fermer
if (document.getElementById('closeInfoBtn')) {
    document.getElementById('closeInfoBtn').onclick = function() {
        closeModal('eventInfoModal');
    };
}

// Gestion du double-clic sur les événements
document.querySelectorAll('.event-wrapper').forEach(cell => {
    cell.addEventListener('dblclick', function() {
        // Récupération des données
        document.getElementById('infoNomEvenement').textContent = this.dataset.nom || 'Non spécifié';
        document.getElementById('infoMailGroupe').textContent = this.dataset.mail || 'Non spécifié';
        document.getElementById('infoTelGroupe').textContent = this.dataset.tel || 'Non spécifié';
        document.getElementById('infoReferentGroupe').textContent = this.dataset.referent || 'Non spécifié';
        
        // Affichage du modal
        openModal('eventInfoModal');
    });
});

// Fermeture des modals en cliquant en dehors
window.onclick = function(event) {
    if (event.target === document.getElementById('createEventModal')) {
        closeModal('createEventModal');
    }
    if (event.target === document.getElementById('eventInfoModal')) {
        closeModal('eventInfoModal');
    }
};

// Code existant pour la sélection des cellules
let selectedCells = [];

document.querySelectorAll('.cell-clickable').forEach(cell => {
    cell.addEventListener('click', function() {
        this.classList.toggle('selected');
        const parentCell = this.closest('.calendar-cell');
        parentCell.classList.toggle('selected');
        
        const cellData = {
            date: this.dataset.date,
            moment: this.dataset.moment,
            salle: this.dataset.salle
        };
        
        const index = selectedCells.findIndex(c =>
            c.date === cellData.date && c.moment === cellData.moment && c.salle === cellData.salle
        );
        
        if (index > -1) {
            selectedCells.splice(index, 1);
        } else {
            selectedCells.push(cellData);
        }
    });
});

// Gestion pour le sélecteur de semaine
document.getElementById('weekSelector').addEventListener('change', function() {
    document.getElementById('weekSelectorForm').submit();
});

// Gestion des actions d'ajout/suppression
let currentAction = 'add';

window.setAction = function(action) {
    currentAction = action;
    document.getElementById('occupation-action').value = action;
};

window.prepareSubmit = function() {
    if (selectedCells.length === 0) {
        alert("Veuillez sélectionner au moins une cellule.");
        return false;
    }
    
    document.getElementById('multi-occupations-data').value = JSON.stringify(selectedCells);
    document.getElementById('occupation-action').value = currentAction;
    
    // Rendre l'event obligatoire uniquement si on ajoute
    const eventSelect = document.getElementById('eventSelect');
    if (currentAction === 'add') {
        eventSelect.required = true;
    } else {
        eventSelect.required = false;
    }
    
    return true;
};
});