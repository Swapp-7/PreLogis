@extends('layouts.app')

@section('title', 'Gestion des Groupes')

@section('content')
<link rel="stylesheet" href="{{ asset('css/groupe-index.css') }}">

<div class="container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1><i class="fas fa-users"></i> Gestion des Groupes</h1>
                <p class="subtitle">Gérez les groupes de résidents et leurs affectations aux chambres</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('groups.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Groupe
                </a>
            </div>
        </div>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
    @endif

    <!-- Statistiques -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $groups->count() }}</div>
                <div class="stat-label">Groupes créés</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bed"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $groups->sum('chambres_count') }}</div>
                <div class="stat-label">Chambres occupées</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $groups->where('chambres_count', '>', 0)->count() }}</div>
                <div class="stat-label">Groupes actifs</div>
            </div>
        </div>
    </div>

    
    
    @if ($groups->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>Aucun groupe créé</h3>
        <p>Commencez par créer votre premier groupe de résidents</p>
        <a href="{{ route('groups.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Créer un groupe
        </a>
    </div>
    @else
    <div class="groups-grid" id="groupsContainer">
        @foreach($groups as $group)
        <div class="group-card" data-status="{{ $group->chambres_count > 0 ? 'active' : 'inactive' }}">
            <div class="group-header">
                <div class="group-photo">
                    @if($group->PHOTORESIDENT && file_exists(public_path('storage/photos/' . $group->PHOTORESIDENT)))
                        <img src="{{ asset('storage/photos/' . $group->PHOTORESIDENT) }}" alt="Photo du groupe">
                    @else
                        <div class="default-photo">
                            <i class="fas fa-users"></i>
                        </div>
                    @endif
                </div>
                <div class="group-status">
                    @if($group->chambres_count > 0)
                        <span class="status-badge active">
                            <i class="fas fa-check-circle"></i> Actif
                        </span>
                    @else
                        <span class="status-badge inactive">
                            <i class="fas fa-pause-circle"></i> Inactif
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="group-content">
                <h3 class="group-name">{{ $group->NOMRESIDENT }}</h3>
                
                <div class="group-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>{{ $group->MAILRESIDENT ?: 'Non renseigné' }}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>{{ $group->TELRESIDENT ?: 'Non renseigné' }}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-bed"></i>
                        <span>
                            @if($group->chambres_count > 0)
                                {{ $group->chambres_count }} chambre{{ $group->chambres_count > 1 ? 's' : '' }}
                            @else
                                Aucune chambre assignée
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="group-actions">
                <a href="{{ route('groups.show', $group->IDRESIDENT) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i> Détails
                </a>
                
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>



<script>
// Fonction de recherche
function filterGroups() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.group-card');
    
    cards.forEach(card => {
        const groupName = card.querySelector('.group-name').textContent.toLowerCase();
        const email = card.querySelector('.info-item:nth-child(1) span').textContent.toLowerCase();
        
        if (groupName.includes(searchTerm) || email.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Fonction de filtrage par statut
function filterByStatus(status) {
    const cards = document.querySelectorAll('.group-card');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Mise à jour des boutons de filtre
    filterButtons.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-filter="${status}"]`).classList.add('active');
    
    // Filtrage des cartes
    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        
        if (status === 'all' || cardStatus === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

</script>
@endsection