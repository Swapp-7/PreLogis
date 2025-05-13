<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>

    <body>
        <header>
            <div class="page">
                <div class="barre-navigation">
                    <ul>
                        <li><a href="{{ url('/') }}"><i class="fas fa-home"></i><span class="text"></span></a></li>

                        <li><a href="{{ url('/Batiment') }}"><i class="fas fa-building"></i><span class="text"></span></a></li>
                        <li><a href="{{ url('/ChambreLibre') }}"><i class="fas fa-bed"></i><span class="text"></span></a></li>
                        <li><a href="{{ url('/LesResidents') }}"><i class="fas fa-users"></i><span class="text"></span></a></li>
                        <li><a href="{{ url('/Salle') }}"><i class="fas fa-door-open"></i><span class="text"></span></a></li>
                        <li><a href="{{ url('/les-salles') }}"><i class="fa-solid fa-calendar-days"></i><span class="text"></span></a></li>
                        <li><a href="{{ url('/archive') }}"><i class="fa-solid fa-box-archive"></i><span class="text"></span></a></li>
                        <li><a href="{{ url('/planning-resident') }}"><i class="fa-solid fa-calendar-check"></i><span class="text"></span></a></li>
                        
                    </ul>
                </div>
                
                <div class="containerpage">
                    @yield('content')
                </div>
            </div>
            
            <style>
               /* Styles pour la barre de navigation uniquement */
.barre-navigation {
    width: 80px;
    background-color: #112233; /* Fond bleu foncé plus sombre que le fond principal */
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
    border-right: 2px solid #FDC11F; /* Bordure jaune sur le côté droit */
}

.barre-navigation ul {
    list-style: none;
    padding: 0;
    margin: 20px 0 0 0;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 35px; /* Espacement plus grand entre les icônes */
}

.barre-navigation ul li {
    width: 100%;
    display: flex;
    justify-content: center;
}

.barre-navigation ul li a {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: #CDCBCE; /* Gris clair comme les cartes */
    font-size: 14px;
    padding: 12px 0;
    width: 100%;
    transition: all 0.3s ease;
    position: relative;
}

.barre-navigation ul li a i {
    font-size: 28px;
    transition: transform 0.3s ease, color 0.3s ease;
}

.barre-navigation ul li a:hover i {
    color: #FDC11F; /* Jaune au survol */
    transform: translateY(-3px); /* Déplacement vers le haut au survol */
}

/* Indicateur de page active */
.barre-navigation ul li a.active {
    position: relative;
}

.barre-navigation ul li a.active i {
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

/* Effet de brillance au survol */
.barre-navigation ul li a::after {
    content: '';
    position: absolute;
    bottom: -5px;
    width: 0;
    height: 2px;
    background-color: #FDC11F;
    transition: width 0.3s ease;
}

.barre-navigation ul li a:hover::after {
    width: 60%;
}

/* Responsive design pour la barre de navigation */
@media (max-width: 768px) {
    .barre-navigation {
        width: 60px;
    }
    
    .containerpage {
        margin-left: 60px;
        width: calc(100% - 60px);
    }
    
    .barre-navigation ul li a i {
        font-size: 24px;
    }
    
    .barre-navigation ul {
        gap: 25px;
    }
}
            </style>
        </header>
    </body>
</html>