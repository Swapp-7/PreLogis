@extends('layouts.app')

@section('title', 'Nouveau Résident')

@section('content')
<link rel="stylesheet" href="{{ asset('css/nouveau-resident.css') }}">

<div class="container">
    <div class="page-header">
        <h1>Bâtiment {{$chambre->IDBATIMENT}} - Chambre {{$chambre->NUMEROCHAMBRE}}</h1>
        <a href="{{ route('chambre', ['IdBatiment' => $chambre->IDBATIMENT]) }}" class="btn-return">
            <i class="fas fa-arrow-left"></i> Retour aux chambres
        </a>
    </div>
    
    <div class="page-title">
        <i class="fas fa-user-plus"></i>
        <h2>Ajouter un Occupant</h2>
    </div>
    
    <div class="form-tabs">
        <button type="button" class="tab-button active" data-target="resident-form">
            <i class="fas fa-user"></i> Résident individuel
        </button>
        <button type="button" class="tab-button" data-target="group-form">
            <i class="fas fa-users"></i> Groupe
        </button>
        <button type="button" class="tab-button" data-target="existing-group-form">
            <i class="fas fa-user-plus"></i> Membre de groupe existant
        </button>
    </div>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <!-- Formulaire pour résident individuel -->
    <form id="resident-form" method="POST" action="{{ route('resident.store', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" enctype="multipart/form-data" class="tab-content active">
        @csrf
        @method('POST')
        <input type="hidden" name="type" value="individual">
        
        <div class="form-layout">
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-user"></i>
                    <h3>Informations personnelles</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span> :</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Téléphone <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="tel" name="tel" value="{{ old('tel') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="anniversaire">Date de Naissance <span class="required">*</span> :</label>
                        <input type="date" class="form-control" id="anniversaire" name="anniversaire" value="{{ old('anniversaire') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="nationalite">Nationalité <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="nationalite" name="nationalite" value="{{ old('nationalite') }}" required>
                    </div>
                </div>
                
                <div class="section-header">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Études</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="etablissement">Établissement :</label>
                        <input type="text" class="form-control" id="etablissement" name="etablissement" value="{{ old('etablissement') }}" >
                    </div>
                    <div class="form-group">
                        <label for="annee_etude">Année d'étude <span class="required">*</span> :</label>
                        <select class="form-control" id="annee_etude" name="annee_etude" >
                            <option value="" {{ old('annee_etude') == '' ? 'selected' : '' }}>Non renseigné</option>

                            <option value="1re" {{ old('annee_etude') == '1re' ? 'selected' : '' }}>1re</option>
                            <option value="2e" {{ old('annee_etude') == '2e' ? 'selected' : '' }}>2e</option>
                            <option value="3e" {{ old('annee_etude') == '3e' ? 'selected' : '' }}>3e</option>
                            <option value="4e" {{ old('annee_etude') == '4e' ? 'selected' : '' }}>4e</option>
                            <option value="5e" {{ old('annee_etude') == '5e' ? 'selected' : '' }}>5e</option>
                            <option value="6e" {{ old('annee_etude') == '6e' ? 'selected' : '' }}>6e</option>
                            <option value="7e" {{ old('annee_etude') == '7e' ? 'selected' : '' }}>7e</option>
                            <option value="jeune travailleur" {{ old('annee_etude') == 'jeune travailleur' ? 'selected' : '' }}>jeune travailleur</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_entree">Date d'entrée <span class="required">*</span> :</label>
                        @php
                            // Détermine la date minimale d'entrée en fonction du résident actuel ou du dernier futur résident
                            $minDate = now()->format('Y-m-d');
                            $infoMessage = null;
                            if ($dateEntreeSuggestion){
                                $minDate = \Carbon\Carbon::parse($dateEntreeSuggestion)->addDay()->format('Y-m-d');
                            }
                            
                        @endphp
                        <input type="date" class="form-control" id="date_entree" name="date_entree" 
                               value="{{ old('date_entree', $minDate ?? $minDate) }}" 
                               min="{{ $minDate }}" 
                               required>
                        @if($infoMessage)
                        <small class="form-text help-text">
                            <i class="fas fa-info-circle"></i> {{ $infoMessage }}
                        </small>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="date_depart">Date de départ (optionnel) :</label>
                        <input type="date" class="form-control" id="date_depart" name="date_depart" 
                               value="{{ old('date_depart') }}"
                               min="{{ old('date_entree', $dateEntreeSuggestion ?? $minDate) }}">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="photo">Photo :</label>
                        <div class="photo-upload-container">
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <div class="upload-preview">
                                <img id="photo-preview" src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg" alt="Aperçu photo">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-home"></i>
                    <h3>Adresse</h3>
                </div>
                
                <input type="hidden" name="adresse[idadresse]" value="">
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="adresse">Rue <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="adresse" name="adresse[adresse]" value="{{ old('adresse.adresse') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal">Code Postal <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="code_postal" name="adresse[code_postal]" value="{{ old('adresse.code_postal') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="ville" name="adresse[ville]" value="{{ old('adresse.ville') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pays">Pays <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="pays" name="adresse[pays]" value="{{ old('adresse.pays', 'France') }}" required>
                    </div>
                </div>
                
                <div class="section-header">
                    <i class="fas fa-users"></i>
                    <h3>Informations des parents</h3>
                </div>
                
                @for($i = 0; $i < 2; $i++)
                <div class="parent-block">
                    <div class="parent-number">Parent {{ $i+1 }}</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom :</label>
                            <input type="text" class="form-control" name="parents[{{ $i }}][nom]" value="{{ old('parents.'.$i.'.nom') }}">
                        </div>
                        <div class="form-group">
                            <label>Téléphone :</label>
                            <input type="text" class="form-control" name="parents[{{ $i }}][tel]" value="{{ old('parents.'.$i.'.tel') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Profession :</label>
                            <input type="text" class="form-control" name="parents[{{ $i }}][profession]" value="{{ old('parents.'.$i.'.profession') }}">
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Ajouter le résident
            </button>
        </div>
    </form>
    
    <!-- Formulaire pour groupe -->
    <form id="group-form" method="POST" action="{{ route('resident.store', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" enctype="multipart/form-data" class="tab-content">
        @csrf
        @method('POST')
        <input type="hidden" name="type" value="group">
        
        <div class="form-layout">
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-users"></i>
                    <h3>Informations du groupe</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="group_nom">Nom du groupe <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="group_nom" name="nom" value="{{ old('nom') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span> :</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Téléphone <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="tel" name="tel" value="{{ old('tel') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="photo_group">Photo du groupe :</label>
                        <div class="photo-upload-container">
                            <input type="file" class="form-control" id="photo_group" name="photo" accept="image/*">
                            <div class="upload-preview">
                                <img id="photo-preview-group" src="https://cdn-icons-png.flaticon.com/512/166/166258.png" alt="Aperçu photo du groupe">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_entree_group">Date d'entrée <span class="required">*</span> :</label>
                        @php
                            // Détermine la date minimale d'entrée en fonction du résident actuel ou du dernier futur résident
                            $minDate = now()->format('Y-m-d');
                            $infoMessage = null;
                            if ($dateEntreeSuggestion){
                                $minDate = \Carbon\Carbon::parse($dateEntreeSuggestion)->addDay()->format('Y-m-d');
                            }
                        @endphp
                        <input type="date" class="form-control" id="date_entree_group" name="date_entree" 
                               value="{{ old('date_entree', $minDate ?? $minDate) }}" 
                               min="{{ $minDate }}" 
                               required>
                        @if($infoMessage)
                        <small class="form-text help-text">
                            <i class="fas fa-info-circle"></i> {{ $infoMessage }}
                        </small>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="date_depart_group">Date de départ (optionnel) :</label>
                        <input type="date" class="form-control" id="date_depart_group" name="date_depart" 
                               value="{{ old('date_depart') }}"
                               min="{{ old('date_entree', $dateEntreeSuggestion ?? $minDate) }}">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-home"></i>
                    <h3>Adresse</h3>
                </div>
                
                <input type="hidden" name="adresse[idadresse]" value="">
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="adresse_group">Rue <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="adresse_group" name="adresse[adresse]" value="{{ old('adresse.adresse') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal_group">Code Postal <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="code_postal_group" name="adresse[code_postal]" value="{{ old('adresse.code_postal') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="ville_group">Ville <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="ville_group" name="adresse[ville]" value="{{ old('adresse.ville') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pays_group">Pays <span class="required">*</span> :</label>
                        <input type="text" class="form-control" id="pays_group" name="adresse[pays]" value="{{ old('adresse.pays', 'France') }}" required>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-users"></i> Créer le groupe
            </button>
        </div>
    </form>
    
    <!-- Formulaire pour membre de groupe existant -->
    <form id="existing-group-form" method="POST" action="{{ route('resident.store', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" enctype="multipart/form-data" class="tab-content">
        @csrf
        @method('POST')
        <input type="hidden" name="type" value="group_member">
        
        <div class="form-layout">
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-users"></i>
                    <h3>Sélection du groupe existant</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="existing_group_id">Groupe <span class="required">*</span> :</label>
                        <select class="form-control" id="existing_group_id" name="existing_group_id" required>
                            <option value="">-- Sélectionnez un groupe --</option>
                            @php
                                $groups = \App\Models\Resident::where('TYPE', 'group')->orderBy('NOMRESIDENT')->get();
                            @endphp
                            @foreach($groups as $group)
                                <option value="{{ $group->IDRESIDENT }}">{{ $group->NOMRESIDENT }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_entree_member">Date d'entrée <span class="required">*</span> :</label>
                        <input type="date" class="form-control" id="date_entree_member" name="date_entree" 
                               value="{{ old('date_entree', $minDate ?? now()->format('Y-m-d')) }}" 
                               min="{{ $minDate }}" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="date_depart_member">Date de départ (optionnel) :</label>
                        <input type="date" class="form-control" id="date_depart_member" name="date_depart" 
                               value="{{ old('date_depart') }}"
                               min="{{ old('date_entree', $minDate) }}">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Ajouter au groupe
            </button>
        </div>
    </form>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    
   
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons et contenus
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Afficher le contenu correspondant
            const target = this.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
        });
    });

    // Aperçu photo
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    const dateEntree = document.getElementById('date_entree');
    const dateDepart = document.getElementById('date_depart');

    if (dateEntree && dateDepart) {
        // Mettre à jour la date minimale de départ quand la date d'entrée change
        dateEntree.addEventListener('change', function() {
            dateDepart.min = dateEntree.value;
            validateDates();
        });
        
        // Valider les dates lors de la modification de la date de départ
        dateDepart.addEventListener('change', validateDates);
        
        // Fonction de validation des dates simplifiée
        function validateDates() {
            // Vérifier uniquement la relation entre date d'entrée et date de départ
            if (dateDepart.value && dateEntree.value) {
                if (new Date(dateDepart.value) <= new Date(dateEntree.value)) {
                    dateDepart.setCustomValidity('La date de départ doit être après la date d\'entrée');
                } else {
                    dateDepart.setCustomValidity('');
                }
            } else {
                dateDepart.setCustomValidity('');
            }
        }
        
        // Validation initiale
        validateDates();
    }
});
</script>

<style>
/* Style pour les champs obligatoires */
.required {
    color: #e74c3c;
    font-weight: bold;
    margin-left: 2px;
}
</style>
@endsection