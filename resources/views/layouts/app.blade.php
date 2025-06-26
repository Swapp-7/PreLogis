<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title')</title>
        
        <!-- Preload critical resources -->
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" as="style">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        
        <style>
            /* Critical CSS pour éviter le flash blanc */
            html, body {
                margin: 0;
                padding: 0;
                font-family: 'Roboto', sans-serif;
                background-color: #20364B !important;
                color: #FFFFFF;
                overflow-x: hidden;
            }
            
            /* Styles de base pour la page */
            .page {
                display: flex;
                min-height: 100vh;
                background-color: #20364B;
            }

            .containerpage {
                margin-left: 70px;
                width: calc(100% - 70px);
                padding: 20px;
                box-sizing: border-box;
                background-color: #20364B;
            }

            /* Styles pour la barre de navigation */
            .barre-navigation {
                width: 70px;
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
                padding: 15px 0;
                border-right: 2px solid #FDC11F;
                transition: width 0.3s ease;
                overflow-y: auto;
                /* Masquer la scrollbar */
                -ms-overflow-style: none;  /* Internet Explorer 10+ */
                scrollbar-width: none;  /* Firefox */
            }
            
            /* Masquer la scrollbar pour WebKit (Chrome, Safari, Edge) */
            .barre-navigation::-webkit-scrollbar {
                display: none;
            }

            .barre-navigation:hover {
                width: 220px;
            }

            /* Sections de navigation */
            .nav-section {
                width: 100%;
                margin-bottom: 15px;
            }

            .nav-section.main-nav {
                margin-bottom: 25px;
                border-bottom: 1px solid rgba(253, 193, 31, 0.2);
                padding-bottom: 15px;
            }

            /* Titres de section */
            .nav-section-title {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 8px 0;
                margin-bottom: 10px;
                border-bottom: 1px solid rgba(253, 193, 31, 0.1);
            }

            .nav-section-title i {
                font-size: 20px;
                color: #FDC11F;
                min-width: 25px;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .barre-navigation:hover .nav-section-title i {
                opacity: 1;
            }

            .section-text {
                opacity: 0;
                transform: translateX(-10px);
                transition: opacity 0.3s ease, transform 0.3s ease;
                margin-left: 10px;
                font-weight: 600;
                color: #FDC11F;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .barre-navigation:hover .section-text {
                opacity: 1;
                transform: translateX(0);
            }

            .barre-navigation ul {
                list-style: none;
                padding: 0;
                margin: 0;
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }

            .barre-navigation ul li {
                width: 100%;
                display: flex;
                justify-content: center;
            }

            .barre-navigation ul li a {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                text-decoration: none;
                color: #CDCBCE;
                font-size: 12px;
                padding: 8px 0;
                padding-left: 20px;
                width: 100%;
                position: relative;
                white-space: nowrap;
                overflow: hidden;
                border-radius: 0 15px 15px 0;
                margin-right: 10px;
                transition: background-color 0.3s ease;
            }

            .barre-navigation ul li a:hover {
                background-color: rgba(253, 193, 31, 0.05);
            }

            .barre-navigation ul li a i {
                font-size: 22px;
                min-width: 25px;
                transition: color 0.3s ease;
            }

            .barre-navigation ul li a:hover i {
                color: #FDC11F;
            }

            .nav-text {
                opacity: 0;
                transform: translateX(-10px);
                transition: opacity 0.3s ease, transform 0.3s ease;
                margin-left: 10px;
                font-weight: 500;
                color: #CDCBCE;
            }

            .barre-navigation:hover .nav-text {
                opacity: 1;
                transform: translateX(0);
            }

            /* Indicateur de page active */
            .barre-navigation ul li a.active {
                background-color: rgba(253, 193, 31, 0.15);
                border-left: 3px solid #FDC11F;
            }

            .barre-navigation ul li a.active i {
                color: #FDC11F;
            }

            .barre-navigation ul li a.active .nav-text {
                color: #FDC11F;
                font-weight: 600;
            }

            /* Logo en haut de la barre de navigation */
            .nav-logo {
                margin-bottom: 20px;
            }

            .nav-logo img {
                width: 40px;
                height: auto;
            }

            /* Style pour les boutons de paramètres et déconnexion */
            .settings-container {
                width: 100%;
                padding: 8px 0;
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
                padding: 8px 0;
                padding-left: 20px;
                cursor: pointer;
                color: #CDCBCE;
                font-size: 12px;
                text-decoration: none;
                position: relative;
                white-space: nowrap;
                overflow: hidden;
                border-radius: 0 15px 15px 0;
                margin-right: 10px;
                transition: background-color 0.3s ease;
            }

            .settings-btn:hover {
                background-color: rgba(253, 193, 31, 0.05);
            }

            .settings-btn i {
                font-size: 22px;
                min-width: 25px;
                transition: color 0.3s ease;
            }

            .settings-btn:hover i {
                color: #FDC11F;
            }

            .settings-btn.active {
                background-color: rgba(253, 193, 31, 0.15);
                border-left: 3px solid #FDC11F;
            }

            .settings-btn.active i {
                color: #FDC11F;
            }

            /* Style pour le bouton de déconnexion */
            .logout-container {
                width: 100%;
                padding: 8px 0 15px 0;
                border-top: none;
            }

            .logout-btn {
                background: none;
                border: none;
                display: flex;
                align-items: center;
                justify-content: flex-start;
                width: 100%;
                padding: 8px 0;
                padding-left: 20px;
                cursor: pointer;
                color: #CDCBCE;
                font-size: 12px;
                font-family: 'Roboto', sans-serif;
                position: relative;
                white-space: nowrap;
                overflow: hidden;
                border-radius: 0 15px 15px 0;
                margin-right: 10px;
                transition: background-color 0.3s ease;
            }

            .logout-btn:hover {
                background-color: rgba(231, 76, 60, 0.1);
            }

            .logout-btn i {
                font-size: 22px;
                min-width: 25px;
                color: #e74c3c;
                transition: color 0.3s ease;
            }

            .logout-btn:hover i {
                color: #ff6b6b;
            }

            /* Responsive design pour la barre de navigation */
            @media (max-width: 768px) {
                .barre-navigation {
                    width: 55px;
                }
                
                .barre-navigation:hover {
                    width: 55px;
                }
                
                .containerpage {
                    margin-left: 55px;
                    width: calc(100% - 55px);
                    padding: 10px;
                }
                
                .barre-navigation ul li a i {
                    font-size: 20px;
                }
                
                .nav-section-title i {
                    font-size: 18px;
                    opacity: 0;
                }
                
                .barre-navigation:hover .nav-section-title i {
                    opacity: 0;
                }
                
                .barre-navigation ul {
                    gap: 6px;
                }
                
                .barre-navigation:hover .nav-text {
                    opacity: 0;
                }
                
                .barre-navigation:hover .section-text {
                    opacity: 0;
                }
                
                .barre-navigation:hover .logout-btn .nav-text {
                    opacity: 0;
                }
                
                .nav-section {
                    margin-bottom: 10px;
                }
                
                .nav-section.main-nav {
                    margin-bottom: 15px;
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
            
            /* Loader pour les transitions de page */
            .page-loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: #20364B;
                z-index: 9999;
                display: none;
                justify-content: center;
                align-items: center;
            }
            
            .page-loader.active {
                display: flex;
            }
            
            .loader-spinner {
                width: 50px;
                height: 50px;
                border: 3px solid rgba(253, 193, 31, 0.3);
                border-top: 3px solid #FDC11F;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>

    <body>
        <!-- Loader pour les transitions -->
        <div class="page-loader" id="pageLoader">
            <div class="loader-spinner"></div>
        </div>
        
        <header>
            <div class="page">
                <div class="barre-navigation">
                    <!-- Section principale -->
                    <div class="nav-section main-nav">
                        <ul>
                            <li><a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
                                <i class="fas fa-home"></i><span class="nav-text">Accueil</span>
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Section logement -->
                    <div class="nav-section">
                        <div class="nav-section-title">
                            <i class="fas fa-home"></i>
                            <span class="section-text">Logement</span>
                        </div>
                        <ul>
                            <li><a href="{{ url('/Batiment') }}" class="{{ request()->is('Batiment') || request()->is('Batiment/*') ? 'active' : '' }}">
                                <i class="fas fa-building"></i><span class="nav-text">Bâtiments</span>
                            </a></li>
                            
                            <li><a href="{{ url('/ChambreLibre') }}" class="{{ request()->is('ChambreLibre') ? 'active' : '' }}">
                                <i class="fas fa-bed"></i><span class="nav-text">Chambres</span>
                            </a></li>
                            
                            <li><a href="{{ url('/LesResidents') }}" class="{{ request()->is('LesResidents') ? 'active' : '' }}">
                                <i class="fas fa-users"></i><span class="nav-text">Résidents</span>
                            </a></li>
                            <li><a href="{{ url('/groupes') }}" class="{{ request()->is('groupes') || request()->is('groupes/*') ? 'active' : '' }}">
                                <i class="fas fa-user-friends"></i><span class="nav-text">Groupes</span>
                            </a></li>
                            
                        </ul>
                    </div>
                    
                    <!-- Section salles et événements -->
                    <div class="nav-section">
                        <div class="nav-section-title">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="section-text">Les salles</span>
                        </div>
                        <ul>
                            
                            <li><a href="{{ url('/Salle') }}" class="{{ request()->is('Salle') ? 'active' : '' }}">
                                <i class="fas fa-door-open"></i><span class="nav-text">Salles</span>
                            </a></li>
                            
                            <li><a href="{{ url('/les-salles') }}" class="{{ request()->is('les-salles') ? 'active' : '' }}">
                                <i class="fa-solid fa-calendar-days"></i><span class="nav-text">Planning</span>
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Section rapports -->
                    <div class="nav-section">
                        <div class="nav-section-title">
                            <i class="fas fa-chart-line"></i>
                            <span class="section-text">Rapports</span>
                        </div>
                        <ul>
                            <li><a href="{{ url('/planning-resident') }}" class="{{ request()->is('planning-resident') ? 'active' : '' }}">
                                <i class="fa-solid fa-people-arrows"></i><span class="nav-text">Mouvements</span>
                            </a></li>
                            
                            <li><a href="{{ url('/archive') }}" class="{{ request()->is('archive') ? 'active' : '' }}">
                                <i class="fa-solid fa-box-archive"></i><span class="nav-text">Archives</span>
                            </a></li>
                        </ul>
                    </div>
                    
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
        </header>
        
        <script>
            // Script pour améliorer les transitions de page
            document.addEventListener('DOMContentLoaded', function() {
                // Cacher le loader une fois la page chargée
                const loader = document.getElementById('pageLoader');
                if (loader) {
                    loader.classList.remove('active');
                }
                
                // Intercepter les clics sur les liens de navigation
                const navLinks = document.querySelectorAll('.barre-navigation a, .nav-text');
                navLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href') || this.closest('a').getAttribute('href');
                        
                        // Ne pas intercepter les liens externes ou les boutons de formulaire
                        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                            return;
                        }
                        
                        // Ne pas intercepter si c'est la page actuelle
                        if (href === window.location.pathname + window.location.search) {
                            e.preventDefault();
                            return;
                        }
                        
                        // Afficher le loader
                        if (loader) {
                            loader.classList.add('active');
                        }
                        
                        // Laisser le navigateur continuer la navigation normale
                    });
                });
                
                // Gérer le bouton retour du navigateur
                window.addEventListener('pageshow', function(event) {
                    if (loader) {
                        loader.classList.remove('active');
                    }
                });
                
                // Fallback pour cacher le loader après un délai maximum
                setTimeout(function() {
                    if (loader) {
                        loader.classList.remove('active');
                    }
                }, 2000);
            });
            
            // Préchargement des pages importantes
            const importantPages = [
                '{{ url("/") }}',
                '{{ url("/Batiment") }}',
                '{{ url("/ChambreLibre") }}',
                '{{ url("/LesResidents") }}',
                '{{ url("/groupes") }}',
                '{{ url("/Salle") }}',
                '{{ url("/les-salles") }}'
            ];
            
            // Précharger les pages au survol
            document.addEventListener('DOMContentLoaded', function() {
                const navLinks = document.querySelectorAll('.barre-navigation a');
                navLinks.forEach(link => {
                    link.addEventListener('mouseenter', function() {
                        const href = this.getAttribute('href');
                        if (href && importantPages.includes(href)) {
                            const linkElement = document.createElement('link');
                            linkElement.rel = 'prefetch';
                            linkElement.href = href;
                            document.head.appendChild(linkElement);
                        }
                    });
                });
            });
        </script>
    </body>
</html>