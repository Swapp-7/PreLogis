<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <div class="page">
                <div class="barre-navigation">
                    <ul>
                        <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                            <i class="fas fa-home"></i><span class="nav-text">Accueil</span>
                        </a></li>
                
                        <li><a href="{{ url('/Batiment') }}" class="{{ request()->is('Batiment') || request()->is('Batiment/*') ? 'active' : '' }}">
                            <i class="fas fa-building"></i><span class="nav-text">Bâtiments</span>
                        </a></li>
                        
                        <li><a href="{{ url('/ChambreLibre') }}" class="{{ request()->is('ChambreLibre') ? 'active' : '' }}">
                            <i class="fas fa-bed"></i><span class="nav-text">Chambres libres</span>
                        </a></li>
                        
                        <li><a href="{{ url('/LesResidents') }}" class="{{ request()->is('LesResidents') ? 'active' : '' }}">
                            <i class="fas fa-users"></i><span class="nav-text">Résidents</span>
                        </a></li>
                        
                        <li><a href="{{ url('/groupes') }}" class="{{ request()->is('groupes') || request()->is('groupes/*') ? 'active' : '' }}">
                            <i class="fas fa-user-friends"></i><span class="nav-text">Groupes</span>
                        </a></li>
                        
                        <li><a href="{{ url('/Salle') }}" class="{{ request()->is('Salle') ? 'active' : '' }}">
                            <i class="fas fa-door-open"></i><span class="nav-text">Salles</span>
                        </a></li>
                        
                        <li><a href="{{ url('/les-salles') }}" class="{{ request()->is('les-salles') ? 'active' : '' }}">
                            <i class="fa-solid fa-calendar-days"></i><span class="nav-text">Planning</span>
                        </a></li>
                        
                        <li><a href="{{ url('/archive') }}" class="{{ request()->is('archive') ? 'active' : '' }}">
                            <i class="fa-solid fa-box-archive"></i><span class="nav-text">Archives</span>
                        </a></li>
                        
                        <li><a href="{{ url('/planning-resident') }}" class="{{ request()->is('planning-resident') ? 'active' : '' }}">
                            <i class="fa-solid fa-people-arrows"></i><span class="nav-text">Départs / Arrivées</span>
                        </a></li>
                    </ul>
                    
                    <!-- Settings button -->
                    <div class="settings-container">
                        <a href="{{ url('/parametres') }}" class="{{ request()->is('parametres') ? 'active' : '' }} settings-btn">
                            <i class="fas fa-cog"></i>
                            <span class="nav-text">Paramètres</span>
                        </a>
                    </div>
                    
                    <!-- Logout button -->
                    <div class="logout-container">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="nav-text">Déconnexion</span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="containerpage">
                    @yield('content')
                </div>
            </div>
            
            <style>
               /* Styles de base pour la page */
/* Styles de base pour la page - INCHANGÉS */
body {
    margin: 0;
    padding: 0;
    font-family: 'Roboto', sans-serif;
    background-color: #20364B;
    color: #FFFFFF;
}

.page {
    display: flex;
    min-height: 100vh;
}

.containerpage {
    margin-left: 80px;
    width: calc(100% - 80px);
    padding: 20px;
    box-sizing: border-box;
}

/* Styles pour la barre de navigation - SANS ANIMATION AU CHARGEMENT */
.barre-navigation {
    width: 80px;
    background-color: #112233;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    box-shadow: 3px 0 10px rgba(0, 0, 0, 0.3);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 10;
    padding: 25px 0;
    border-right: 2px solid #FDC11F;
    transition: width 0.3s ease;
}

.barre-navigation:hover {
    width: 200px;
}

.barre-navigation ul {
    list-style: none;
    padding: 0;
    margin: 20px 0 0 0;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 35px;
}

.barre-navigation ul li {
    width: 100%;
    display: flex;
    justify-content: center;
    /* Supprimer toute animation potentielle */
    animation: none !important;
}

.barre-navigation ul li a {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    text-decoration: none;
    color: #CDCBCE;
    font-size: 14px;
    padding: 12px 0;
    padding-left: 25px;
    width: 100%;
    position: relative;
    white-space: nowrap;
    overflow: hidden;
}

.barre-navigation ul li a i {
    font-size: 24px;
    min-width: 30px;
    transition: color 0.3s ease;
}

.barre-navigation ul li a:hover i {
    color: #FDC11F;
}

/* MODIFICATION: Éliminer l'animation au chargement pour le texte */
.nav-text {
    /* Supprimer les propriétés qui causent l'animation au chargement */
    opacity: 0; /* Garder à 0 car on veut qu'il soit invisible au début */
    /* Supprimer la transformation initiale */
    transform: none;
    /* Garder la transition pour l'effet hover */
    transition: opacity 0.3s ease;
    margin-left: 12px;
    font-weight: 500;
    color: #CDCBCE;
}

.barre-navigation:hover .nav-text {
    opacity: 1;
    /* Pas besoin de transformer car il n'y a pas de position initiale décalée */
}

/* Indicateur de page active */
.barre-navigation ul li a.active {
    position: relative;
    background-color: rgba(253, 193, 31, 0.1);
}

.barre-navigation ul li a.active i {
    color: #FDC11F;
}

.barre-navigation ul li a.active .nav-text {
    color: #FDC11F;
}

.barre-navigation ul li a.active::before {
    content: '';
    position: absolute;
    left: 0;
    height: 100%;
    width: 3px;
    background-color: #FDC11F;
}

/* Effet de brillance au survol - SIMPLIFIÉ */
.barre-navigation ul li a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    /* Pas de transition de largeur initiale - elle sera déclenchée uniquement au hover */
    width: 0;
    height: 2px;
    background-color: #FDC11F;
    transition: width 0.3s ease;
}

.barre-navigation ul li a:hover::after {
    width: 60%;
}

/* Logo en haut de la barre de navigation */
.nav-logo {
    margin-bottom: 30px;
}

.nav-logo img {
    width: 50px;
    height: auto;
}

/* Style pour les boutons de paramètres et déconnexion */
.settings-container {
    width: 100%;
    padding: 10px 0;
    margin-top: auto;
    border-top: 1px solid rgba(253, 193, 31, 0.3);
}

.settings-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    padding: 12px 0;
    padding-left: 25px;
    cursor: pointer;
    color: #CDCBCE;
    font-size: 14px;
    text-decoration: none;
    position: relative;
    white-space: nowrap;
    overflow: hidden;
}

.settings-btn i {
    font-size: 24px;
    min-width: 30px;
    transition: color 0.3s ease;
}

.settings-btn:hover i {
    color: #FDC11F;
}

.settings-btn::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    width: 0;
    height: 2px;
    background-color: #FDC11F;
    transition: width 0.3s ease;
}

.settings-btn:hover::after {
    width: 60%;
}

/* Style pour le bouton de déconnexion */
.logout-container {
    width: 100%;
    padding: 10px 0 20px 0;
    border-top: none;
}

.logout-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
    padding: 12px 0;
    padding-left: 25px;
    cursor: pointer;
    color: #CDCBCE;
    font-size: 14px;
    font-family: 'Roboto', sans-serif;
    position: relative;
    white-space: nowrap;
    overflow: hidden;
}

.logout-btn i {
    font-size: 24px;
    min-width: 30px;
    color: #e74c3c;
    transition: color 0.3s ease;
}

.logout-btn:hover i {
    color: #FDC11F;
}

.logout-btn::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    width: 0;
    height: 2px;
    background-color: #FDC11F;
    transition: width 0.3s ease;
}

.logout-btn:hover::after {
    width: 60%;
}

/* Responsive design pour la barre de navigation */
@media (max-width: 768px) {
    .barre-navigation {
        width: 60px;
    }
    
    .barre-navigation:hover {
        width: 60px;
    }
    
    .containerpage {
        margin-left: 60px;
        width: calc(100% - 60px);
        padding: 10px;
    }
    
    .barre-navigation ul li a i {
        font-size: 22px;
    }
    
    .barre-navigation ul {
        gap: 25px;
    }
    
    .barre-navigation:hover .nav-text {
        opacity: 0;
    }
    
    .barre-navigation:hover .logout-btn .nav-text {
        opacity: 0;
    }
}

/* Styles pour les éléments de notification dans la barre de navigation */
.nav-notification {
    position: absolute;
    top: 5px;
    right: 10px;
    background-color: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
            </style>
        </header>
    </body>
</html>