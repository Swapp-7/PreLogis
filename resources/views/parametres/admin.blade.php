@extends('layouts.app')

@section('title', 'Paramètres du compte administrateur')

@section('content')
<div class="parametres-container">
    <div class="header-container">
        <h1 class="page-title">Gestion des comptes</h1>
        <a href="{{ route('parametres') }}" class="btn-retour">
            <i class="fas fa-arrow-left"></i> Retour aux paramètres
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
   
    <!-- Section Modifier Email Admin -->
    <div class="section-container">
        <div class="section-header">
            <h2>Modifier l'adresse email administrateur</h2>
        </div>
        
        <div class="form-container">
            <form action="{{ route('parametres.admin.updateEmail') }}" method="POST" class="form-admin">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="info-value">Identifiant : {{$admin->NOMUTILISATEUR}}</label>
                </div>
                <div class="form-group">
                    <label for="email">Nouvelle adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $admin->EMAIL) }}" required maxlength="255">
                    @if ($errors->has('email'))
                        <span class="form-error">{{ $errors->first('email') }}</span>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="password">Confirmez votre mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    @if ($errors->has('password'))
                        <span class="form-error">{{ $errors->first('password') }}</span>
                    @endif
                    <span class="form-help">Pour des raisons de sécurité, veuillez entrer votre mot de passe actuel pour confirmer le changement.</span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-valider">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Créer un nouveau compte utilisateur -->
    <div class="section-container">
        <div class="section-header">
            <h2>Créer un nouveau compte administrateur</h2>
        </div>
        
        <div class="form-container">
            <form action="{{ route('parametres.admin.users.create') }}" method="POST" class="form-admin">
                @csrf
                
                <div class="form-group">
                    <label for="name">Nom d'utilisateur</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
                    @if ($errors->has('name'))
                        <span class="form-error">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="user_email">Adresse email</label>
                    <input type="email" id="user_email" name="email" value="{{ old('email') }}" required maxlength="255">
                    @if ($errors->has('email'))
                        <span class="form-error">{{ $errors->first('email') }}</span>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="user_password">Mot de passe</label>
                    <input type="password" id="user_password" name="password" required minlength="6">
                    @if ($errors->has('password'))
                        <span class="form-error">{{ $errors->first('password') }}</span>
                    @endif
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-valider">Créer le compte</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Liste des utilisateurs -->
    <div class="section-container">
        <div class="section-header">
            <h2>Liste des comptes administrateurs</h2>
        </div>
        
        <div class="users-container">
            @forelse($users as $user)
            @if ($user->NOMUTILISATEUR === $admin->NOMUTILISATEUR)
            <div class="user-card">
                <div class="user-info">
                    <div class="user-name">{{ $user->NOMUTILISATEUR }}</div>
                    <div class="user-email">{{ $user->EMAIL }}</div>
                    <div class="user-role role-admin">
                        <i class="fas fa-user-shield"></i> Administrateur
                    </div>
                </div>
                <div class="user-actions">
                    <button class="btn-modifier" onclick="openEditModal({{ $user->IDADMIN }}, '{{ $user->NOMUTILISATEUR }}', '{{ $user->EMAIL }}')">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    
                </div>
            </div>
            @else
            <div class="user-card">
                <div class="user-info">
                    <div class="user-name">{{ $user->NOMUTILISATEUR }}</div>
                    <div class="user-email">{{ $user->EMAIL }}</div>
                    <div class="user-role role-admin">
                        <i class="fas fa-user-shield"></i> Administrateur
                    </div>
                </div>
                <div class="user-actions">
                    <button class="btn-modifier" onclick="openEditModal({{ $user->IDADMIN }}, '{{ $user->NOMUTILISATEUR }}', '{{ $user->EMAIL }}')">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <button class="btn-supprimer" onclick="openDeleteModal({{ $user->IDADMIN }}, '{{ $user->NOMUTILISATEUR }}')">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>
            @endif

            @empty
            <div class="no-users">
                <i class="fas fa-users"></i>
                <p>Aucun compte administrateur trouvé.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Modifier Utilisateur -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier le compte administrateur</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="edit_name">Nom d'utilisateur</label>
                <input type="text" id="edit_name" name="name" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="edit_email">Adresse email</label>
                <input type="email" id="edit_email" name="email" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="edit_password">Nouveau mot de passe (optionnel)</label>
                <input type="password" id="edit_password" name="password" minlength="6">
                <span class="form-help">Laissez vide pour conserver le mot de passe actuel</span>
            </div>
            
            <div class="form-group">
                <label for="edit_password_confirmation">Confirmer le nouveau mot de passe</label>
                <input type="password" id="edit_password_confirmation" name="password_confirmation" minlength="6">
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-annuler" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn-valider">Modifier</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Supprimer Utilisateur -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer le compte de <strong id="deleteUserName"></strong> ?</p>
            <p class="warning">Cette action est irréversible.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-annuler" onclick="closeDeleteModal()">Annuler</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
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
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .section-header h2 {
        color: #FFFFFF;
        margin: 0;
        font-size: 20px;
    }
    
    .info-container {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .info-item {
        display: flex;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
    }
    
    .info-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-label {
        width: 40%;
        color: #CDCBCE;
        font-weight: 500;
    }
    
    .info-value {
        width: 60%;
        color: #FDC11F;
        font-weight: 600;
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
    
    .form-container {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(253, 193, 31, 0.2);
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
    
    .form-group select {
        cursor: pointer;
    }
    
    .form-group select option {
        background-color: #112233;
        color: #FFFFFF;
    }
    
    .form-error {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 5px;
        display: block;
    }
    
    .form-help {
        color: #95a5a6;
        font-size: 0.8rem;
        margin-top: 5px;
        display: block;
        font-style: italic;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .btn-valider {
        background-color: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
        padding: 10px 20px;
        border-radius: 5px;
        border: 1px solid rgba(46, 204, 113, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-valider:hover {
        background-color: #2ecc71;
        color: #112233;
    }
    
    /* Users list styles */
    .users-container {
        display: grid;
        gap: 15px;
    }
    
    .user-card {
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 15px;
        border: 1px solid rgba(253, 193, 31, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .user-card:hover {
        background-color: rgba(255, 255, 255, 0.08);
        border-color: rgba(253, 193, 31, 0.4);
    }
    
    .user-info {
        flex-grow: 1;
    }
    
    .user-name {
        font-weight: 600;
        color: #FFFFFF;
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .user-email {
        color: #CDCBCE;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .user-role {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .role-admin {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.3);
    }
    
    .role-secretaire {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
        border: 1px solid rgba(52, 152, 219, 0.3);
    }
    
    .role-gestionnaire {
        background-color: rgba(155, 89, 182, 0.2);
        color: #9b59b6;
        border: 1px solid rgba(155, 89, 182, 0.3);
    }
    
    .role-employee {
        background-color: rgba(149, 165, 166, 0.2);
        color: #95a5a6;
        border: 1px solid rgba(149, 165, 166, 0.3);
    }
    
    .user-created {
        color: #95a5a6;
        font-size: 12px;
    }
    
    .user-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-modifier, .btn-supprimer {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-modifier {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
        border: 1px solid rgba(52, 152, 219, 0.3);
    }
    
    .btn-modifier:hover {
        background-color: #3498db;
        color: #112233;
    }
    
    .btn-supprimer {
        background-color: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.3);
    }
    
    .btn-supprimer:hover {
        background-color: #e74c3c;
        color: #FFFFFF;
    }
    
    .no-users {
        text-align: center;
        padding: 40px;
        color: #95a5a6;
    }
    
    .no-users i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        justify-content: center;
        align-items: center;
    }
    
    .modal-content {
        background-color: #112233;
        border-radius: 8px;
        padding: 0;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .modal-header h3 {
        color: #FFFFFF;
        margin: 0;
        font-size: 18px;
    }
    
    .close {
        color: #95a5a6;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .close:hover {
        color: #FDC11F;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px;
        border-top: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .btn-annuler {
        background-color: rgba(149, 165, 166, 0.2);
        color: #95a5a6;
        padding: 10px 20px;
        border-radius: 5px;
        border: 1px solid rgba(149, 165, 166, 0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-annuler:hover {
        background-color: #95a5a6;
        color: #112233;
    }
    
    .warning {
        color: #e67e22;
        font-weight: 500;
        font-size: 14px;
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
        
        .user-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .user-actions {
            align-self: flex-end;
        }
        
        .modal-content {
            width: 95%;
            margin: 10px;
        }
    }
</style>

<script>
    // Modal functions
    function openEditModal(userId, name, email) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        
        // Set form action
        form.action = `/parametres/admin/users/${userId}`;
        
        // Fill form fields
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';
        
        // Show modal
        modal.style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    function openDeleteModal(userId, name) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        
        // Set form action
        form.action = `/parametres/admin/users/${userId}`;
        
        // Set user name
        document.getElementById('deleteUserName').textContent = name;
        
        // Show modal
        modal.style.display = 'flex';
    }
    
    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (event.target === editModal) {
            closeEditModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });
    
    // Form validation for password confirmation
    document.addEventListener('DOMContentLoaded', function() {
        const editForm = document.getElementById('editForm');
        
        editForm.addEventListener('submit', function(e) {
            const password = document.getElementById('edit_password').value;
            const passwordConfirmation = document.getElementById('edit_password_confirmation').value;
            
            if (password && password !== passwordConfirmation) {
                e.preventDefault();
                alert('La confirmation du mot de passe ne correspond pas.');
                return false;
            }
        });
    });
</script>
@endsection
