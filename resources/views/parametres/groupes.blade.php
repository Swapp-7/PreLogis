@extends('layouts.app')

@section('title', 'Gestion des Groupes')

@section('content')
<div class="parametres-container">
    <div class="header-container">
        <h1 class="page-title">Gestion des Groupes</h1>
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
            <h2>Liste des Groupes</h2>
            <button class="btn-ajouter" id="btn-ajouter-groupe">
                <i class="fas fa-plus"></i> Ajouter un groupe
            </button>
        </div>
        
        <!-- Formulaire pour ajouter un groupe (initialement caché) -->
        <div class="form-container" id="form-ajouter-groupe" style="display: none;">
            <form action="{{ route('nouvelEvenement') }}" method="POST" class="form-groupe">
                @csrf
                <h3>Ajouter un nouveau groupe</h3>
                <div class="form-group">
                    <label for="nomEvenement">Nom du groupe</label>
                    <input type="text" id="nomEvenement" name="nomEvenement" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="mailGroupe">Email du groupe</label>
                    <input type="email" id="mailGroupe" name="mailGroupe" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="telGroupe">Téléphone du groupe</label>
                    <input type="text" id="telGroupe" name="telGroupe" required placeholder="Ex: +33 6 12 34 56 78">
                </div>
                <div class="form-group">
                    <label for="referentGroupe">Référent du groupe</label>
                    <input type="text" id="referentGroupe" name="referentGroupe" required maxlength="255">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-annuler" id="btn-annuler-ajout">Annuler</button>
                    <button type="submit" class="btn-valider">Ajouter</button>
                </div>
            </form>
        </div>
        
        <!-- Tableau des groupes -->
        <div class="table-container">
            <table class="table-groupes">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Référent</th>
                        <th>Couleur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupes as $groupe)
                    <tr data-id="{{ $groupe->IDEVENEMENT }}">
                        <td>{{ $groupe->NOMEVENEMENT }}</td>
                        <td>{{ $groupe->MAILGROUPE }}</td>
                        <td>{{ $groupe->TELGROUPE }}</td>
                        <td>{{ $groupe->REFERENTGROUPE }}</td>
                        <td>
                            <div class="color-preview" style="background-color: {{ $groupe->COULEUR }};"></div>
                        </td>
                        <td class="actions">
                            <button class="btn-action btn-edit" data-id="{{ $groupe->IDEVENEMENT }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" data-id="{{ $groupe->IDEVENEMENT }}">
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
            <p>Êtes-vous sûr de vouloir supprimer ce groupe ?</p>
            <p>Toutes ses occupations seront supprimé !</p>
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

<!-- Modal pour modifier un groupe -->
<div class="modal" id="modal-edit" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier le groupe</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit-nomEvenement">Nom du groupe</label>
                    <input type="text" id="edit-nomEvenement" name="nomEvenement" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="edit-mailGroupe">Email du groupe</label>
                    <input type="email" id="edit-mailGroupe" name="mailGroupe" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="edit-telGroupe">Téléphone du groupe</label>
                    <input type="text" id="edit-telGroupe" name="telGroupe" required>
                </div>
                <div class="form-group">
                    <label for="edit-referentGroupe">Référent du groupe</label>
                    <input type="text" id="edit-referentGroupe" name="referentGroupe" required maxlength="255">
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
    
    .table-groupes {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-groupes th, .table-groupes td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .table-groupes th {
        background-color: rgba(253, 193, 31, 0.1);
        color: #FDC11F;
        font-weight: 500;
    }
    
    .table-groupes tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .color-preview {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
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
        // Gestion du formulaire d'ajout de groupe
        const btnAjouterGroupe = document.getElementById('btn-ajouter-groupe');
        const formAjouterGroupe = document.getElementById('form-ajouter-groupe');
        const btnAnnulerAjout = document.getElementById('btn-annuler-ajout');
        
        btnAjouterGroupe.addEventListener('click', function() {
            formAjouterGroupe.style.display = 'block';
            btnAjouterGroupe.style.display = 'none';
        });
        
        btnAnnulerAjout.addEventListener('click', function() {
            formAjouterGroupe.style.display = 'none';
            btnAjouterGroupe.style.display = 'inline-flex';
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
                const groupeId = this.getAttribute('data-id');
                deleteForm.action = `/parametres/groupes/${groupeId}`;
                modalDelete.style.display = 'flex';
            });
        });
        
        // Gestion de la modification
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const groupeId = this.getAttribute('data-id');
                const tr = document.querySelector(`tr[data-id="${groupeId}"]`);
                
                // Récupérer les valeurs actuelles
                const nom = tr.cells[0].textContent;
                const email = tr.cells[1].textContent;
                const tel = tr.cells[2].textContent;
                const referent = tr.cells[3].textContent;
                
                // Remplir le formulaire
                document.getElementById('edit-nomEvenement').value = nom;
                document.getElementById('edit-mailGroupe').value = email;
                document.getElementById('edit-telGroupe').value = tel;
                document.getElementById('edit-referentGroupe').value = referent;
                
                // Définir l'action du formulaire
                editForm.action = `/parametres/groupes/${groupeId}`;
                
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
