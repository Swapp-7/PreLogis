@extends('layouts.app')

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
        <h2>Ajouter un Résident</h2>
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
    
    <form method="POST" action="{{ route('resident.store', ['IdBatiment' => $chambre->IDBATIMENT,'NumChambre' => $chambre->NUMEROCHAMBRE]) }}" enctype="multipart/form-data">
        @csrf
        @method('POST')
        
        <div class="form-layout">
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-user"></i>
                    <h3>Informations personnelles</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom :</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom :</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Téléphone :</label>
                        <input type="text" class="form-control" id="tel" name="tel" value="{{ old('tel') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="anniversaire">Date de Naissance :</label>
                        <input type="date" class="form-control" id="anniversaire" name="anniversaire" value="{{ old('anniversaire') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="nationalite">Nationalité :</label>
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
                        <input type="text" class="form-control" id="etablissement" name="etablissement" value="{{ old('etablissement') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="annee_etude">Année d'étude :</label>
                        <select class="form-control" id="annee_etude" name="annee_etude" required>
                            <option value="1re" {{ old('annee_etude') == '1re' ? 'selected' : '' }}>1re</option>
                            <option value="2e" {{ old('annee_etude') == '2e' ? 'selected' : '' }}>2e</option>
                            <option value="3e" {{ old('annee_etude') == '3e' ? 'selected' : '' }}>3e</option>
                            <option value="4e" {{ old('annee_etude') == '4e' ? 'selected' : '' }}>4e</option>
                            <option value="5e" {{ old('annee_etude') == '5e' ? 'selected' : '' }}>5e</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_entree">Date d'entrée :</label>
                        <input type="date" class="form-control" id="date_entree" name="date_entree" 
                               value="{{ old('date_entree', $dateEntreeSuggestion) }}" 
                               min="{{ isset($residentPartant) && $residentPartant ? \Carbon\Carbon::parse($residentPartant->DATEDEPART)->addDay()->format('Y-m-d') : now()->format('Y-m-d') }}" 
                               required>
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
                        <label for="adresse">Rue :</label>
                        <input type="text" class="form-control" id="adresse" name="adresse[adresse]" value="{{ old('adresse.adresse') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal">Code Postal :</label>
                        <input type="text" class="form-control" id="code_postal" name="adresse[code_postal]" value="{{ old('adresse.code_postal') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville :</label>
                        <input type="text" class="form-control" id="ville" name="adresse[ville]" value="{{ old('adresse.ville') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pays">Pays :</label>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endsection