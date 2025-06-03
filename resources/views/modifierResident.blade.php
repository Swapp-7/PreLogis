@extends('layouts.app')

@section('title', 'Modifier un Résident')

@section('content')
<link rel="stylesheet" href="{{ asset('css/modifier-resident.css') }}">
<div class="container">
    <div class="page-header">
        <h2>
            @if($resident->TYPE == 'group')
                Modifier un Groupe
            @else
                Modifier un Résident
            @endif
        </h2>
        <a href="javascript:history.back()" class="btn-return">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
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

    <form method="POST" action="{{ route('resident.update', ['idResident' => $resident->IDRESIDENT]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-layout">
            <!-- Colonne gauche - Identité -->
            <div class="form-section">
                <div class="section-header">
                    @if($resident->TYPE == 'group')
                        <i class="fas fa-users"></i>
                        <h3>Informations du Groupe</h3>
                    @else
                        <i class="fas fa-user"></i>
                        <h3>Identité</h3>
                    @endif
                </div>
                
                <div class="photo-upload">
                    <div class="current-photo">
                        @if($resident->PHOTO == "photo" || !$resident->PHOTO)
                            @if($resident->TYPE == 'group')
                                <img src="https://cdn-icons-png.flaticon.com/512/166/166258.png" alt="Photo actuelle du groupe">
                            @else
                                <img src="https://st3.depositphotos.com/6672868/13701/v/450/depositphotos_137014128-stock-illustration-user-profile-icon.jpg" alt="Photo actuelle">
                            @endif
                        @else
                            <img src="{{ asset('storage/' . $resident->PHOTO) }}" alt="Photo actuelle">
                        @endif
                    </div>
                    <div class="upload-control">
                        <label for="photo">
                            @if($resident->TYPE == 'group')
                                Modifier la photo du groupe :
                            @else
                                Modifier la photo :
                            @endif
                        </label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">
                            @if($resident->TYPE == 'group')
                                Nom du groupe :
                            @else
                                Nom :
                            @endif
                        </label>
                        <input type="text" class="form-control" id="nom" name="nom" value="{{$resident->NOMRESIDENT}}" required>
                    </div>
                    @if($resident->TYPE != 'group')
                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="{{$resident->PRENOMRESIDENT}}" required>
                        </div>
                    @endif
                </div>
                
                @if($resident->TYPE != 'group')
                <div class="form-row">
                    <div class="form-group">
                        <label for="anniversaire">Date de Naissance :</label>
                        <input type="date" class="form-control" id="anniversaire" name="anniversaire" value="{{ $resident->DATENAISSANCE }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="nationalite">Nationalité :</label>
                        <input type="text" class="form-control" id="nationalite" name="nationalite" value="{{$resident->NATIONALITE}}" required>
                    </div>
                </div>
                @endif
                
                @if($resident->TYPE != 'group')
                <div class="section-header">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Études</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="etablissement">Établissement :</label>
                        <input type="text" class="form-control" id="etablissement" name="etablissement" value="{{$resident->ETABLISSEMENT}}" required>
                    </div>
                    <div class="form-group">
                        <label for="annee_etude">Année d'étude :</label>
                        <select class="form-control" id="annee_etude" name="annee_etude" required>
                            <option value="1re" {{ $resident->ANNEEETUDE == '1re' || $resident->ANNEEETUDE == '1ère' ? 'selected' : '' }}>1re</option>
                            <option value="2e"  {{ $resident->ANNEEETUDE == '2e' || $resident->ANNEEETUDE == '2ème' ? 'selected' : '' }}>2e</option>
                            <option value="3e"  {{ $resident->ANNEEETUDE == '3e' || $resident->ANNEEETUDE == '3ème' ? 'selected' : '' }}>3e</option>
                            <option value="4e"  {{ $resident->ANNEEETUDE == '4e' || $resident->ANNEEETUDE == '4ème' ? 'selected' : '' }}>4e</option>
                            <option value="5e"  {{ $resident->ANNEEETUDE == '5e' || $resident->ANNEEETUDE == '5ème' ? 'selected' : '' }}>5e</option>
                        </select>
                    </div>
                </div>
                @endif
                
            </div>
            
            <!-- Colonne droite - Contacts et adresse -->
            <div class="form-section">
                <div class="section-header">
                    <i class="fas fa-address-card"></i>
                    <h3>Coordonnées</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{$resident->MAILRESIDENT}}" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Téléphone :</label>
                        <input type="text" class="form-control" id="tel" name="tel" value="{{ $resident->TELRESIDENT }}" required>
                    </div>
                </div>
                
                <div class="section-header">
                    <i class="fas fa-home"></i>
                    <h3>Adresse</h3>
                </div>
                
                <input type="hidden" name="adresse[idadresse]" value="{{ $resident->adresse->IDADRESSE }}">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="adresse">Rue :</label>
                        <input type="text" class="form-control" id="adresse" name="adresse[adresse]" value="{{$resident->adresse->ADRESSE}}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="code_postal">Code Postal :</label>
                        <input type="text" class="form-control" id="code_postal" name="adresse[code_postal]" value="{{$resident->adresse->CODEPOSTAL}}" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville :</label>
                        <input type="text" class="form-control" id="ville" name="adresse[ville]" value="{{$resident->adresse->VILLE}}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pays">Pays :</label>
                        <input type="text" class="form-control" id="pays" name="adresse[pays]" value="{{$resident->adresse->PAYS}}" required>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Section Parents (uniquement pour les résidents individuels) -->
        @if($resident->TYPE != 'group')
        <div class="form-section parents-section">
            <div class="section-header">
                <i class="fas fa-users"></i>
                <h3>Informations des parents</h3>
            </div>
            
            @if($resident->parents && count($resident->parents) > 0)
                @foreach($resident->parents as $parent)
                <div class="parent-block">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom :</label>
                            <input type="text" class="form-control" name="parents[{{ $loop->index }}][nom]" value="{{ $parent->NOMPARENT }}" required>
                        </div>
                        <div class="form-group">
                            <label>Téléphone :</label>
                            <input type="text" class="form-control" name="parents[{{ $loop->index }}][tel]" value="{{ $parent->TELPARENT }}" required>
                        </div>
                        <div class="form-group">
                            <label>Profession :</label>
                            <input type="text" class="form-control" name="parents[{{ $loop->index }}][profession]" value="{{ $parent->PROFESSION }}" required>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <p class="no-data">Aucun parent enregistré</p>
            @endif
        </div>
        @endif
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
<script>
     document.addEventListener('DOMContentLoaded', function() {
        // Aperçu photo si disponible
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.querySelector('.current-photo img');
                        if (img) {
                            img.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endsection