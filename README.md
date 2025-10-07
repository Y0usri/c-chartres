# Projet C-Chartres Web

Application Symfony 7.3 de gestion de joueurs multi-sports, avec système d'avis, back-office administrateur, pagination, filtres dynamiques et calcul optimisé des moyennes.

## Sommaire
- [Technologies](#technologies)
- [Prérequis](#prérequis)
- [Démarrage rapide](#démarrage-rapide)
- [Installation](#installation)
- [Configuration](#configuration)
- [Commandes utiles](#commandes-utiles)
- [Comptes de test](#comptes-de-test)
- [Fonctionnalités](#fonctionnalités)
- [Sécurité & Rôles](#sécurité--rôles)
- [Utilisation rapide](#utilisation-rapide)

## Technologies
- PHP 8.2+
- Symfony 7.3
- Doctrine ORM / Migrations
- Twig
- Symfony Security / Validation / Form
- Bootstrap 5 (CDN)
- Symfony UX Turbo (désactivé partiellement là où nécessaire)
- PHPUnit (tests basiques)

## Prérequis
- PHP 8.2+
- Composer
- Serveur MariaDB / MySQL

## Démarrage rapide
```bash
git clone <repo>
cd c-chartres-web
cp .env.example .env.local
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --no-interaction
symfony serve -d   # ou php -S localhost:8000 -t public
```
Comptes de test : admin@example.com / admin123 et user@example.com / user123

## Installation
Cloner ou récupérer le projet puis installer les dépendances :
```bash
composer install
```
Si vous utilisez l'exécutable Symfony :
```bash
symfony composer install
```

## Configuration
Copier le fichier d'exemple :
```
cp .env.example .env.local
```
Puis éditer les valeurs (DB, APP_SECRET). Exemple de `.env.local` :
```
DATABASE_URL="mysql://USER:PASS@HOST:3306/cchartresweb?serverVersion=10.5.8-MariaDB&charset=utf8mb4"
```
Créer la base puis exécuter migrations & fixtures :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --no-interaction
```

## Commandes utiles
Lancer le serveur de dev :
```bash
symfony serve -d
# ou
php -S localhost:8000 -t public
```

Lancer les tests :
```bash
php bin/phpunit
```

Forcer rechargement des fixtures (AJOUT sans purge) :
```bash
php bin/console doctrine:fixtures:load --append
```

ATTENTION: si vous avez besoin d'une réinitialisation propre avec contraintes FK, purgez manuellement dans l'ordre (`review` puis `player` etc.) ou utilisez un script SQL, car le `--purge-with-truncate` peut échouer (FK actives).

## Comptes de test
| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@example.com | admin123 |
| User  | user@example.com  | user123  |

## Fonctionnalités
Front / Public :
- Page d'accueil listant les joueurs avec pagination (12 / page)
- Filtres combinés : recherche (prénom/nom), catégorie, niveau, moyenne minimum
- Calcul de la moyenne optimisé par requête d'agrégation (repository dédié)
- Détails joueur avec liste des avis triés récents → anciens
- Système d'avis (1 avis par joueur et par utilisateur, unicité contrôlée par contrainte DB + UniqueEntity)
- Empêchement de doublon même en conditions de race (try/catch sur violation)

Espace utilisateur :
- Page "Mes avis" listant les avis de l'utilisateur connecté

Back-office Admin :
- Dashboard avec KPIs (joueurs, avis, catégories, niveaux, utilisateurs, moyenne globale)
- Top joueurs (moyenne + nombre d'avis)
- Accès rapides (création / listes)
- CRUD Catégorie / Niveau / Joueur (upload image basique – nom de fichier stocké)

Technique & Qualité :
- Validation (Assert + UniqueEntity)
- Sécurité (form_login, rôle ADMIN, filtrage /admin et /my)
- Gestion CSRF (formulaires sensibles + avis)
- Seed data (fixtures) incluant joueurs connus multi-sports
- Tests (Repository, Validation Review, HomeController)
- Styling via Bootstrap + custom CSS

## Sécurité & Rôles
- ROLE_USER : ajouter un avis, consulter "Mes avis"
- ROLE_ADMIN : tout le back-office (`/admin/*`)
- Contrôle d'accès défini dans `security.yaml`

## Structure base (tables principales)
- `app_user` (utilisateurs)
- `category`
- `level`
- `player`
- `review` (contrainte unique `(user_id, player_id)`)

Relations clés :
- Player -> Category (ManyToOne)
- Player -> Level (ManyToOne)
- Player -> Review (OneToMany)
- Review -> User (ManyToOne)
- Review -> Player (ManyToOne)

## Utilisation rapide
1. Accéder à `/` : liste paginée + filtres.
2. Cliquer sur un joueur : page détail + avis.
3. Se connecter (compte user) pour ajouter un avis (1 seul par joueur).
4. Consulter "Mes avis" via la navigation.
5. Se connecter en admin et visiter `/admin/dashboard` pour les statistiques.
6. Gérer catégories, niveaux et joueurs via le menu Admin.

## Exemples d'URL utiles
```
/?page=2&category=1&level=3&minAvg=3&q=le
/player/5
/admin/player
/admin/dashboard
/my/reviews (-> user_reviews route)
```

## Tests
Commande :
```bash
php bin/phpunit
```
Les tests couvrent :
- Repository moyenne joueur
- Validation d'une Review (note hors plage)
- Accès page d'accueil (contrôleur)

## Déploiement rapide (exemple minimal)
1. Copier `.env` vers `.env.prod.local` et ajuster `APP_ENV=prod`.
2. Lancer migrations : `php bin/console doctrine:migrations:migrate --no-interaction --env=prod`.
3. Installer dépendances avec `--no-dev --optimize-autoloader`.
4. Vider cache : `php bin/console cache:clear --env=prod`.

## Contribution interne
Projet figé (version finale). Pour correctif éventuel : créer une branche courte puis merger.

## Licence
Projet pédagogique interne (non publié sous licence open source formelle pour le moment).

---
© 2025 C-Chartres Web
