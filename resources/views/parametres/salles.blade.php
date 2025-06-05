@extends('layouts.app')

@section('title', 'Gestion des Salles')

@section('content')
<div class="parametres-container">
    <div class="header-container">
        <h1 class="page-title">Gestion des Salles</h1>
        <a href="{{ route('parametres') }}" class="btn-retour">
            <i class="fas fa-arrow-left"></i> Retour aux Paramètres
        </a>
    </div>
    
    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert-error">
        {{ session('error') }}
    </div>
    @endif
    
    <div class="section-container">
        <div class="section-header">
            <h2>Liste des Salles</h2>
            <button class="btn-ajouter" id="btn-ajouter-salle">
                <i class="fas fa-plus"></i> Ajouter une salle
            </button>
        </div>
        
        <!-- Formulaire pour ajouter une salle (initialement caché) -->
        <div class="form-container" id="form-ajouter-salle" style="display: none;">
            <form action="{{ route('parametres.salles.store') }}" method="POST" class="form-salle">
                @csrf
                <h3>Ajouter une nouvelle salle</h3>
                <div class="form-group">
                    <label for="libelle">Nom de la salle</label>
                    <input type="text" id="libelle" name="libelle" required maxlength="100" placeholder="Ex: Salle de réunion A">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-annuler" id="btn-annuler-ajout">Annuler</button>
                    <button type="submit" class="btn-valider">Ajouter</button>
                </div>
            </form>
        </div>
        
        <!-- Tableau des salles -->
        <div class="table-container">
            <table class="table-salles">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de la salle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salles as $salle)
                    <tr data-id="{{ $salle->IDSALLE }}">
                        <td>{{ $salle->IDSALLE }}</td>
                        <td>{{ $salle->LIBELLESALLE }}</td>
                        <td class="actions">
                            <button class="btn-action btn-edit" data-id="{{ $salle->IDSALLE }}" title="Modifier la salle">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" data-id="{{ $salle->IDSALLE }}" title="Supprimer la salle">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="modal-delete" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette salle ?</p>
            <p>Toutes les occupations associées seront également supprimées !</p>
        </div>
        <div class="modal-footer">
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-supprimer-annuler close-modal">Annuler</button>
                <button type="submit" class="btn-supprimer">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour modifier une salle -->
<div class="modal" id="modal-edit" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier la salle</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit-libelle">Nom de la salle</label>
                    <input type="text" id="edit-libelle" name="libelle" required maxlength="100">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-annuler close-modal">Annuler</button>
                    <button type="submit" class="btn-valider">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .parametres-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .page-title {
        color: #FDC11F;
        font-size: 32px;
        margin: 0;
        border-bottom: 2px solid #FDC11F;
        padding-bottom: 10px;
    }
    
    .btn-retour {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: rgba(253, 193, 31, 0.2);
        color: #FDC11F;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid rgba(253, 193, 31, 0.5);
    }
    
    .btn-retour:hover {
        background-color: #FDC11F;
        color: #112233;
    }
    
    .section-container {
        background-color: #112233;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(253, 193, 31, 0.2);
        margin-bottom: 30px;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .section-header h2 {
        color: #FFFFFF;
        margin: 0;
        font-size: 20px;
    }
    
    .btn-ajouter {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        padding: 8px 15px;
        border-radius: 5px;
        border: 1px solid rgba(46, 204, 113, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-ajouter:hover {
        background-color: #2ecc71;
        color: #FFFFFF;
    }
    
    .form-container {
        margin-bottom: 30px;
    }
    
    .form-salle {
        background-color: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 5px;
        border: 1px solid rgba(253, 193, 31, 0.1);
    }
    
    .form-salle h3 {
        color: #FDC11F;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        color: #FFFFFF;
        margin-bottom: 8px;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        background-color: #1A2A3A;
        border: 1px solid rgba(253, 193, 31, 0.2);
        color: #FFFFFF;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-valider, .btn-annuler {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-valider {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        border: 1px solid rgba(46, 204, 113, 0.5);
    }
    
    .btn-valider:hover {
        background-color: #2ecc71;
        color: #FFFFFF;
    }
    
    .btn-annuler {
        background-color: rgba(149, 165, 166, 0.2);
        color: #95a5a6;
        border: 1px solid rgba(149, 165, 166, 0.5);
    }
    
    .btn-annuler:hover {
        background-color: #95a5a6;
        color: #FFFFFF;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .table-salles {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-salles th, .table-salles td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .table-salles th {
        background-color: rgba(253, 193, 31, 0.1);
        color: #FDC11F;
        font-weight: 500;
    }
    
    .table-salles tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-edit {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
    }
    
    .btn-edit:hover {
        background-color: #3498db;
        color: #FFFFFF;
    }
    
    .btn-delete {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }
    
    .btn-delete:hover {
        background-color: #e74c3c;
        color: #FFFFFF;
    }
    
    .alert-success, .alert-error {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .alert-success {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        border: 1px solid rgba(46, 204, 113, 0.3);
    }
    
    .alert-error {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.3);
    }
    
    /* Modal styles */
    .modal {
        display: flex;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background-color: #112233;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .modal-header h3 {
        color: #FDC11F;
        margin: 0;
    }
    
    .close-modal {
        background: none;
        border: none;
        color: #CDCBCE;
        font-size: 24px;
        cursor: pointer;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-body p {
        color: #CDCBCE;
        margin-bottom: 10px;
    }
    
    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid rgba(253, 193, 31, 0.2);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .btn-supprimer {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid rgba(231, 76, 60, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
    }
    
    .btn-supprimer:hover {
        background-color: #e74c3c;
        color: #FFFFFF;
    }
    
    .btn-supprimer-annuler {
        background-color: rgba(149, 165, 166, 0.2);
        color: #95a5a6;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid rgba(149, 165, 166, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
    }
    
    .btn-supprimer-annuler:hover {
        background-color: #95a5a6;
        color: #FFFFFF;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .parametres-container {
            padding: 15px;
        }
        
        .header-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .btn-retour {
            align-self: flex-start;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du formulaire d'ajout de salle
        const btnAjouterSalle = document.getElementById('btn-ajouter-salle');
        const formAjouterSalle = document.getElementById('form-ajouter-salle');
        const btnAnnulerAjout = document.getElementById('btn-annuler-ajout');
        
        btnAjouterSalle.addEventListener('click', function() {
            formAjouterSalle.style.display = 'block';
            btnAjouterSalle.style.display = 'none';
        });
        
        btnAnnulerAjout.addEventListener('click', function() {
            formAjouterSalle.style.display = 'none';
            btnAjouterSalle.style.display = 'inline-flex';
        });
        
        // Gestion des modals
        const modalDelete = document.getElementById('modal-delete');
        const modalEdit = document.getElementById('modal-edit');
        const deleteForm = document.getElementById('delete-form');
        const editForm = document.getElementById('edit-form');
        
        // Fermeture des modals
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                modalDelete.style.display = 'none';
                modalEdit.style.display = 'none';
            });
        });
        
        // Gestion de la suppression
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const salleId = this.getAttribute('data-id');
                deleteForm.action = `/parametres/salles/${salleId}`;
                modalDelete.style.display = 'flex';
            });
        });
        
        // Gestion de la modification
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const salleId = this.getAttribute('data-id');
                const tr = document.querySelector(`tr[data-id="${salleId}"]`);
                
                // Récupérer les valeurs actuelles
                const libelle = tr.cells[1].textContent;
                
                // Remplir le formulaire
                document.getElementById('edit-libelle').value = libelle;
                
                // Définir l'action du formulaire
                editForm.action = `/parametres/salles/${salleId}`;
                
                // Afficher le modal
                modalEdit.style.display = 'flex';
            });
        });
        
        // Fermer les modals si on clique en dehors
        window.addEventListener('click', function(event) {
            if (event.target === modalDelete) {
                modalDelete.style.display = 'none';
            }
            if (event.target === modalEdit) {
                modalEdit.style.display = 'none';
            }
        });
    });
</script>
@endsection
