# Projet C-Chartres Web

Application Symfony 7.3 de gestion de joueurs, catégories sportives, niveaux et avis utilisateurs.

## Sommaire
- [Technologies](#technologies)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Commandes utiles](#commandes-utiles)
- [Comptes de test](#comptes-de-test)
- [Fonctionnalités](#fonctionnalités)
- [Sécurité & Rôles](#sécurité--rôles)
- [TODO / Améliorations](#todo--améliorations)

## Technologies
- PHP 8.2+
- Symfony 7.3
- Doctrine ORM / Migrations
- Twig
- Symfony Security / Validation / Form

## Prérequis
- PHP 8.2+
- Composer
- Serveur MariaDB / MySQL

## Installation
```bash
composer install
```

## Configuration
Créer un fichier `.env.local` :
```
DATABASE_URL="mysql://USER:PASS@HOST:3306/cchartresweb?serverVersion=10.5.8-MariaDB&charset=utf8mb4"
```
Créer la base puis :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --no-interaction
```

## Commandes utiles
```bash
php -S localhost:8000 -t public
# ou
symfony server:start
```

## Comptes de test
| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@example.com | admin123 |
| User  | user@example.com  | user123  |

## Fonctionnalités
- Liste des joueurs (page d'accueil)
- Fiche joueur + avis + moyenne
- Ajout d'avis (1 par joueur et par utilisateur)
- CRUD Admin: Catégories / Niveaux / Joueurs (upload photo)
- Espace "Mes avis" pour utilisateur connecté
- Validation des données (entités + formulaires)

## Sécurité & Rôles
- ROLE_USER : accès ajout avis + espace personnel
- ROLE_ADMIN : accès interface d'administration `/admin`

## Structure base (principales tables)
- app_user (utilisateurs)
- category
- level
- player
- review (unique user_id + player_id)

## TODO / Améliorations
- Intégrer un framework CSS (Bootstrap / Tailwind)
- Suppression du fichier image lors de la suppression du joueur
- Pagination liste des joueurs
- Calcul moyenne via requête agrégée (optimisation)
- Tests automatisés (PHPUnit) pour services / contrôleurs
- Internationalisation (i18n)

---
© 2025 C-Chartres Web
