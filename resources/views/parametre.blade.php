@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="parametres-container">
    <h1 class="page-title">Paramètres</h1>
    
    <div class="parametres-grid">
        <!-- Section Profil -->
        <div class="parametre-card">
            <div class="parametre-header">
                <i class="fas fa-building"></i>
                <h2>Gestion Batiment</h2>
            </div>
            <div class="parametre-content">
                <p>Ajoutez ou supprimez des bâtiments</p>
                <a href="{{ route('parametres.batiments') }}" class="btn-parametre">Modifier les bâtiments</a>
            </div>
        </div>
        
        <!-- Section Groupes -->
        <div class="parametre-card">
            <div class="parametre-header">
                <i class="fas fa-users"></i>
                <h2>Gestion Groupes</h2>
            </div>
            <div class="parametre-content">
                <p>Ajoutez, modifiez ou supprimez des groupes</p>
                <a href="{{ route('parametres.groupes') }}" class="btn-parametre">Gérer les groupes</a>
            </div>
        </div>
        
        <!-- Section Compte Admin -->
        <div class="parametre-card">
            <div class="parametre-header">
                <i class="fas fa-user-cog"></i>
                <h2>Compte Administrateur</h2>
            </div>
            <div class="parametre-content">
                <p>Modifiez les paramètres de votre compte</p>
                <a href="{{ route('parametres.admin') }}" class="btn-parametre">Gérer mon compte</a>
            </div>
        </div>
        
    </div>
</div>

<style>
    .parametres-container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .page-title {
        color: #FDC11F;
        font-size: 32px;
        margin-bottom: 30px;
        border-bottom: 2px solid #FDC11F;
        padding-bottom: 10px;
    }
    
    .parametres-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
    }
    
    .parametre-card {
        background-color: #112233;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .parametre-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        border-color: rgba(253, 193, 31, 0.5);
    }
    
    .parametre-header {
        background-color: rgba(253, 193, 31, 0.1);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(253, 193, 31, 0.2);
    }
    
    .parametre-header i {
        font-size: 24px;
        margin-right: 15px;
        color: #FDC11F;
    }
    
    .parametre-header h2 {
        font-size: 18px;
        margin: 0;
        color: #FFFFFF;
    }
    
    .parametre-content {
        padding: 20px;
    }
    
    .parametre-content p {
        color: #CDCBCE;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .btn-parametre {
        display: inline-block;
        background-color: rgba(253, 193, 31, 0.2);
        color: #FDC11F;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s ease, color 0.3s ease;
        border: 1px solid rgba(253, 193, 31, 0.5);
        font-weight: 500;
    }
    
    .btn-parametre:hover {
        background-color: #FDC11F;
        color: #112233;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .parametres-grid {
            grid-template-columns: 1fr;
        }
        
        .parametres-container {
            padding: 15px;
        }
        
        .page-title {
            font-size: 24px;
        }
    }
</style>
@endsection