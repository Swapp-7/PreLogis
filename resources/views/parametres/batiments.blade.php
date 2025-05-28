@extends('layouts.app')

@section('title', 'Gestion des Bâtiments')

@section('content')
<div class="parametres-container">
    <div class="header-container">
        <h1 class="page-title">Gestion des Bâtiments</h1>
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
            <h2>Liste des Bâtiments</h2>
            <button class="btn-ajouter" id="btn-ajouter-batiment">
                <i class="fas fa-plus"></i> Ajouter un bâtiment
            </button>
        </div>
        
        <!-- Formulaire pour ajouter un bâtiment (initialement caché) -->
        <div class="form-container" id="form-ajouter-batiment" style="display: none;">
            <form action="{{ route('parametres.batiments.store') }}" method="POST" class="form-batiment">
                @csrf
                <h3>Ajouter un nouveau bâtiment</h3>
                <div class="form-group">
                    <label for="nom">Nom du bâtiment</label>
                    <input type="text" id="nom" name="nom" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="capacite">Capacité</label>
                    <input type="number" id="capacite" name="capacite" required min="0">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-annuler" id="btn-annuler-ajout">Annuler</button>
                    <button type="submit" class="btn-valider">Ajouter</button>
                </div>
            </form>
        </div>
        
        <!-- Tableau des bâtiments -->
        <div class="table-container">
            <table class="table-batiments">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Capacité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batiments as $batiment)
                    <tr data-id="{{ $batiment->IDBATIMENT }}">
                        <td>{{ $batiment->IDBATIMENT }}</td>
                        <td>{{ $batiment->CAPACITE }}</td>
                        <td class="actions">
                            <button class="btn-action btn-add-room" data-id="{{ $batiment->IDBATIMENT }}" title="Ajouter une chambre">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button class="btn-action btn-view-rooms" data-id="{{ $batiment->IDBATIMENT }}" title="Voir les chambres">
                                <i class="fas fa-door-open"></i>
                            </button>
                            <button class="btn-action btn-delete" data-id="{{ $batiment->IDBATIMENT }}" title="Supprimer le bâtiment">
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
            <p>Êtes-vous sûr de vouloir supprimer ce bâtiment ?</p>
            <p>Toutes les chambres associées seront également supprimées !</p>
        </div>
        <div class="modal-footer">
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-annuler close-modal">Annuler</button>
                <button type="submit" class="btn-supprimer">Supprimer</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une chambre -->
<div class="modal" id="modal-add-room" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter une chambre</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-room-form" method="POST">
                @csrf
                <input type="hidden" id="batiment-id" name="batiment_id">
                <div class="form-group">
                    <label for="numero">Numéro de chambre</label>
                    <input type="text" id="numero" name="numero" required maxlength="10">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-annuler close-modal">Annuler</button>
                    <button type="submit" class="btn-valider">Ajouter</button>
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
        color: #112233;
    }
    
    .alert-success {
        background-color: rgba(46, 204, 113, 0.2);
        border: 1px solid rgba(46, 204, 113, 0.5);
        color: #2ecc71;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background-color: rgba(231, 76, 60, 0.2);
        border: 1px solid rgba(231, 76, 60, 0.5);
        color: #e74c3c;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    /* Styles pour le formulaire */
    .form-container {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .form-container h3 {
        color: #FDC11F;
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #CDCBCE;
        font-weight: 500;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: rgba(255, 255, 255, 0.1);
        color: #FFFFFF;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 15px;
    }
    
    .btn-annuler {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid rgba(231, 76, 60, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
    }
    
    .btn-annuler:hover {
        background-color: #e74c3c;
        color: #FFFFFF;
    }
    
    .btn-valider {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid rgba(46, 204, 113, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
    }
    
    .btn-valider:hover {
        background-color: #2ecc71;
        color: #112233;
    }
    
    /* Styles pour le tableau */
    .table-container {
        overflow-x: auto;
    }
    
    .table-batiments {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-batiments th, .table-batiments td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .table-batiments th {
        background-color: rgba(253, 193, 31, 0.1);
        color: #FDC11F;
        font-weight: 500;
    }
    
    .table-batiments tr:hover {
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
        border: 1px solid rgba(52, 152, 219, 0.5);
    }
    
    .btn-edit:hover {
        background-color: #3498db;
        color: #FFFFFF;
    }
    
    .btn-delete {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.5);
    }
    
    .btn-delete:hover {
        background-color: #e74c3c;
        color: #FFFFFF;
    }
    
    /* Styles pour le modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
    }
    
    .modal-content {
        background-color: #20364B;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .modal-header {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        font-size: 13px;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .close-modal:hover {
        color: #FDC11F;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 12px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        border-top: 1px solid rgba(253, 193, 31, 0.2);
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
        // Gestion du formulaire d'ajout de bâtiment
        const btnAjouterBatiment = document.getElementById('btn-ajouter-batiment');
        const formAjouterBatiment = document.getElementById('form-ajouter-batiment');
        const btnAnnulerAjout = document.getElementById('btn-annuler-ajout');
        
        btnAjouterBatiment.addEventListener('click', function() {
            formAjouterBatiment.style.display = 'block';
            btnAjouterBatiment.style.display = 'none';
        });
        
        btnAnnulerAjout.addEventListener('click', function() {
            formAjouterBatiment.style.display = 'none';
            btnAjouterBatiment.style.display = 'inline-flex';
        });
        
        // Gestion des modals
        const modalDelete = document.getElementById('modal-delete');
        const modalAddRoom = document.getElementById('modal-add-room');
        const deleteForm = document.getElementById('delete-form');
        const addRoomForm = document.getElementById('add-room-form');
        
        // Fermeture des modals
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                modalDelete.style.display = 'none';
                modalAddRoom.style.display = 'none';
            });
        });
        
        // Gestion de la suppression
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const batimentId = this.getAttribute('data-id');
                deleteForm.action = `/parametres/batiments/${batimentId}`;
                modalDelete.style.display = 'flex';
            });
        });
        
        // Gestion de l'ajout de chambre
        document.querySelectorAll('.btn-add-room').forEach(btn => {
            btn.addEventListener('click', function() {
                const batimentId = this.getAttribute('data-id');
                
                // Définir l'action du formulaire
                document.getElementById('add-room-form').action = `/parametres/batiments/${batimentId}/chambres`;
                document.getElementById('batiment-id').value = batimentId;
                
                // Afficher le modal
                document.getElementById('modal-add-room').style.display = 'flex';
            });
        });
        
        // Gestion de l'affichage des chambres
        document.querySelectorAll('.btn-view-rooms').forEach(btn => {
            btn.addEventListener('click', function() {
                const batimentId = this.getAttribute('data-id');
                window.location.href = `/parametres/batiments/${batimentId}/chambres`;
            });
        });
        
        // Fermer les modals si on clique en dehors
        window.addEventListener('click', function(event) {
            if (event.target === modalDelete) {
                modalDelete.style.display = 'none';
            }
            if (event.target === modalAddRoom) {
                modalAddRoom.style.display = 'none';
            }
        });
    });
</script>
@endsection