@extends('layouts.app')

@section('title', 'Gestion des Chambres')

@section('content')
<div class="parametres-container">
    <div class="header-container">
        <h1 class="page-title">Gestion des Chambres - Bâtiment {{ $batiment->IDBATIMENT }}</h1>
        <div class="batiment-info">
            <span class="capacite-badge">Capacité: {{ $batiment->CAPACITE }}</span>
            <a href="{{ route('parametres.batiments') }}" class="btn-retour">
                <i class="fas fa-arrow-left"></i> Retour aux Bâtiments
            </a>
        </div>
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
            <h2>Liste des Chambres</h2>
            <button class="btn-ajouter" id="btn-ajouter-chambre">
                <i class="fas fa-plus"></i> Ajouter une chambre
            </button>
        </div>
        
        <!-- Formulaire pour ajouter une chambre (initialement caché) -->
        <div class="form-container" id="form-ajouter-chambre" style="display: none;">
            <form action="{{ route('parametres.chambres.store', $batiment->IDBATIMENT) }}" method="POST" class="form-chambre">
                @csrf
                <h3>Ajouter une nouvelle chambre</h3>
                <div class="form-group">
                    <label for="numero">Numéro de chambre</label>
                    <input type="text" id="numero" name="numero" required maxlength="10">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-annuler" id="btn-annuler-ajout">Annuler</button>
                    <button type="submit" class="btn-valider">Ajouter</button>
                </div>
            </form>
        </div>
        
        <!-- Tableau des chambres -->
        <div class="table-container">
            <table class="table-chambres">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chambres as $chambre)
                    <tr data-id="{{ $chambre->IDCHAMBRE }}">
                        <td>{{ $chambre->NUMEROCHAMBRE }}</td>
                        <td>
                            @if($chambre->IDRESIDENT)
                                <span class="status occupied">Occupée</span>
                            @else
                                <span class="status available">Disponible</span>
                            @endif
                        </td>
                        <td class="actions">
                            @if(!$chambre->IDRESIDENT)
                                <button class="btn-action btn-delete-room" data-id="{{ $chambre->IDCHAMBRE }}" title="Supprimer la chambre">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <button class="btn-action btn-info" title="Voir le résident" data-id="{{ $chambre->IDRESIDENT }}">
                                    <i class="fas fa-user"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="modal-delete-room" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette chambre ?</p>
        </div>
        <div class="modal-footer">
            <form id="delete-room-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-annuler close-modal">Annuler</button>
                <button type="submit" class="btn-supprimer">Supprimer</button>
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
    
    .batiment-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .capacite-badge {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 500;
        border: 1px solid rgba(52, 152, 219, 0.5);
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
    
    .status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status.occupied {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.5);
    }
    
    .status.available {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        border: 1px solid rgba(46, 204, 113, 0.5);
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
    
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: rgba(255, 255, 255, 0.1);
        color: #FFFFFF;
    }
    
    .form-group select option {
        background-color: #20364B;
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
    
    .table-chambres {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-chambres th, .table-chambres td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .table-chambres th {
        background-color: rgba(253, 193, 31, 0.1);
        color: #FDC11F;
        font-weight: 500;
    }
    
    .table-chambres tr:hover {
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
    
    .btn-delete-room {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.5);
    }
    
    .btn-delete-room:hover {
        background-color: #e74c3c;
        color: #FFFFFF;
    }
    
    .btn-info {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
        border: 1px solid rgba(52, 152, 219, 0.5);
    }
    
    .btn-info:hover {
        background-color: #3498db;
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
        
        .batiment-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
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
        // Gestion du formulaire d'ajout de chambre
        const btnAjouterChambre = document.getElementById('btn-ajouter-chambre');
        const formAjouterChambre = document.getElementById('form-ajouter-chambre');
        const btnAnnulerAjout = document.getElementById('btn-annuler-ajout');
        
        btnAjouterChambre.addEventListener('click', function() {
            formAjouterChambre.style.display = 'block';
            btnAjouterChambre.style.display = 'none';
        });
        
        btnAnnulerAjout.addEventListener('click', function() {
            formAjouterChambre.style.display = 'none';
            btnAjouterChambre.style.display = 'inline-flex';
        });
        
        // Gestion des modals
        const modalDeleteRoom = document.getElementById('modal-delete-room');
        const deleteRoomForm = document.getElementById('delete-room-form');
        
        // Fermeture des modals
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                modalDeleteRoom.style.display = 'none';
            });
        });
        
        // Gestion de la suppression
        document.querySelectorAll('.btn-delete-room').forEach(btn => {
            btn.addEventListener('click', function() {
                const chambreId = this.getAttribute('data-id');
                deleteRoomForm.action = `/parametres/chambres/${chambreId}`;
                modalDeleteRoom.style.display = 'flex';
            });
        });
        
        // Fermer les modals si on clique en dehors
        window.addEventListener('click', function(event) {
            if (event.target === modalDeleteRoom) {
                modalDeleteRoom.style.display = 'none';
            }
        });
        
        // Gestion du clic sur le bouton info résident
        document.querySelectorAll('.btn-info').forEach(btn => {
            btn.addEventListener('click', function() {
                const residentId = this.getAttribute('data-id');
                window.location.href = `/residents/${residentId}`;
            });
        });
    });
</script>
@endsection
