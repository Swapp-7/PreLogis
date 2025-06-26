# PreLogis - Système de Gestion de Logements Étudiants

##  Description

PreLogis est une application web développée avec **Laravel 10** destinée à la gestion complète des logements étudiants. L'application permet de gérer les bâtiments, chambres, résidents (individuels et groupes), plannings, occupations de salles et l'archivage des données.

##  Fonctionnalités Principales

###  Gestion des Infrastructures
- **Bâtiments** : Création et gestion des bâtiments avec capacité
- **Chambres** : Attribution automatique des chambres lors de la création des bâtiments
- **Salles** : Gestion des espaces communs et planning d'occupation

###  Gestion des Résidents
- **Résidents Individuels** : Gestion complète des étudiants
- **Groupes** : Gestion des groupes de résidents
- **Futurs Résidents** : Pré-assignation des chambres
- **Archivage Automatique** : Sauvegarde des données des anciens résidents
- **Gestion des Fichiers** : Upload et gestion des documents

###  Planning et Occupation
- **Planning Résidents** : Vue d'ensemble des arrivées et départs
- **Planning Annuel** : Export Excel avec visualisation par couleur
- **Planification des Départs** : Gestion des dates de départ

###  Import/Export
- **Import Excel** : Import en masse des résidents via fichiers Excel
- **Export Excel** : Export des données résidents et planning
- **Export PDF** : Génération de documents (solde de tout compte)

###  Archivage et Recherche
- **Système d'Archives** : Conservation des données historiques


##  Technologies Utilisées

### Backend
- **Laravel 10** - Framework PHP
- **PHP 8.1+** - Langage de programmation
- **MySQL** - Base de données

### Frontend
- **Blade** - Moteur de template Laravel
- **HTML/CSS/JavaScript** - Interface utilisateur
- **Vite** - Bundler et serveur de développement

### Packages Principaux
- **maatwebsite/excel** - Import/Export Excel
- **barryvdh/laravel-dompdf** - Génération PDF
- **doctrine/dbal** - Manipulation base de données avancée

### JavaScript (Frontend)
- **html2canvas** - Export PNG
- **jspdf** - Génération PDF côté client

##  Installation

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- Node.js et npm
- MySQL
- Serveur web (Apache/Nginx)

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone [url-du-repo]
cd PreLogis
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Installer les dépendances JavaScript**
```bash
npm install
```

4. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configuration de la base de données**
Éditer le fichier `.env` avec vos paramètres de base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prelogis
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

6. **Exécuter les migrations**
```bash
php artisan migrate
```

7. **Exécuter les seeders (optionnel)**
```bash
php artisan db:seed
```

8. **Créer le lien symbolique pour le stockage**
```bash
php artisan storage:link
```

9. **Compiler les assets**
```bash
npm run build
# ou pour le développement
npm run dev
```

10. **Démarrer le serveur**
```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

## Structure du Projet

```
PreLogis/
├── app/
│   ├── Console/Commands/        # Commandes Artisan personnalisées
│   ├── Exports/                 # Classes d'export Excel
│   ├── Http/Controllers/        # Contrôleurs
│   ├── Imports/                 # Classes d'import Excel
│   ├── Models/                  # Modèles Eloquent
│   └── Services/                # Services métier
├── config/                      # Fichiers de configuration
├── database/
│   ├── migrations/              # Migrations de base de données
│   └── seeders/                # Seeders
├── public/                      # Fichiers publics
├── resources/
│   ├── views/                   # Templates Blade
│   ├── css/                     # Styles CSS
│   └── js/                      # Scripts JavaScript
├── routes/                      # Définition des routes
└── storage/                     # Stockage des fichiers
```

##  Configuration

### Base de données
Les modèles principaux incluent :
- `Batiment` - Gestion des bâtiments
- `Chambre` - Gestion des chambres
- `Resident` - Gestion des résidents
- `ResidentArchive` - Archives des résidents
- `Salle` - Gestion des salles
- `Occupation` - Planning des salles
- `Evenement` - Événements
- `Parents` - Contacts des parents

### Fichiers de configuration importants
- `config/excel.php` - Configuration des exports Excel
- `config/dompdf.php` - Configuration PDF
- `config/filesystems.php` - Stockage des fichiers

##  Utilisation

### Connexion Administrateur
Accédez à `/admin/login` pour vous connecter à l'interface d'administration.

### Gestion des Bâtiments
1. Créer un bâtiment avec sa capacité
2. Les chambres sont automatiquement créées
3. Assigner les résidents aux chambres

### Gestion des Résidents
1. **Ajouter un résident individuel** : Informations personnelles + parents
2. **Créer un groupe** : Gestion de groupe de résident
3. **Import en masse** : Via fichier Excel
4. **Planifier les départs** : Dates de fin de séjour

### Exports et Rapports
- **Planning annuel** : Vue complète par chambre et date
- **Liste des résidents** : Export Excel filtrable
- **Archives** : Consultation des anciens résidents
- **Solde de tout compte** : Génération PDF

##  Fonctionnalités Avancées

### Système d'Archivage
- Archivage automatique lors de la suppression
- Conservation des données historiques
- Recherche dans les archives
- Résident archivé pendant 3 ans

### Import Excel
- Support des imports en masse
- Validation des données
- Gestion des erreurs détaillée
- Assignation automatique des chambres

### Planning Intelligent
- Gestion des futurs résidents
- Contraintes de dates automatiques
- Visualisation par couleur
- Export Excel avec mise en forme

### Gestion des Groupes
- Occupation multiple de chambres
- Gestion collective des informations
- Archivage spécialisé

##  Sécurité

- Authentification administrateur
- Validation des données d'entrée
- Protection CSRF
- Gestion des permissions

##  Dépannage

### Erreurs courantes

1. **Erreur de permission sur storage/**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

2. **Erreur de clé d'application**
```bash
php artisan key:generate
```

3. **Problème de migration**
```bash
php artisan migrate:refresh
```

##  Commandes Artisan Personnalisées

- `php artisan residents:assign-chambers` - Assigner les chambres aux futurs résidents
- Autres commandes spécifiques disponibles dans `app/Console/Commands/`

##  Contribution

1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Committer vos changements
4. Pusher vers la branche
5. Créer une Pull Request

##  Licence

Ce projet est sous licence Propriétaire .

##  Support

Pour toute question ou problème, contactez l'équipe de développement.

mano.berthet@gmail.com

**Version :** 1.0  
**Dernière mise à jour :** 2025  
**Framework :** Laravel 10.x  
**PHP :** 8.1+

