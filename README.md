# NextRide

Plateforme de location de véhicules développée avec Symfony 6.4 (PHP 8.2), dans le cadre du
projet fil rouge de la formation CDA (Concepteur Développeur d'Applications).

## Fonctionnalités

- Catalogue de véhicules avec recherche et filtres (catégorie, prix)
- Fiche véhicule détaillée avec photo
- Réservation en ligne avec calendrier de disponibilité
- Paiement sécurisé via **Stripe Checkout**
- Génération d'un reçu de réservation
- Authentification (inscription / connexion)
- Espace administrateur : gestion des véhicules (CRUD + upload photo), des réservations et des
  utilisateurs
- Pages légales (mentions légales, politique de confidentialité)

## Stack technique

| Composant | Choix |
|---|---|
| Back-end | Symfony 6.4 (PHP 8.2) |
| Front-end | Twig + CSS + JavaScript (full-stack Symfony) |
| Base de données | MySQL 8.0 (Doctrine ORM) |
| API externe | Stripe (paiement en ligne) |
| Conteneurisation | Docker / Docker Compose |
| CI | GitHub Actions (tests + build Docker) |
| Tests | PHPUnit (unitaires + fonctionnels), DAMA Doctrine Test Bundle |

## Prérequis

- Docker et Docker Compose
- Une clé secrète Stripe de test (compte Stripe gratuit, mode test)

## Installation et lancement (Docker)

1. Cloner le dépôt et se placer dans le dossier `nextride`.
2. Créer un fichier `.env.local` à la racine avec votre clé Stripe de test :
   ```
   STRIPE_SECRET_KEY=sk_test_votre_cle
   ```
3. Lancer les conteneurs :
   ```bash
   docker-compose up -d --build
   ```
4. Installer les dépendances PHP et préparer la base de données :
   ```bash
   docker exec -it nextride_app composer install
   docker exec -it nextride_app php bin/console doctrine:database:create --if-not-exists
   docker exec -it nextride_app php bin/console doctrine:migrations:migrate --no-interaction
   docker exec -it nextride_app php bin/console doctrine:fixtures:load --no-interaction
   ```
5. Accéder à l'application : **http://localhost:8080**
6. Adminer (interface de gestion de la base de données) : **http://localhost:8081**
   (Système : MySQL, Serveur : `database`, Utilisateur : `nextride_user`, Mot de passe :
   `nextride_pass`, Base : `nextride_db`)

## Comptes de test

Créés automatiquement par les fixtures (`doctrine:fixtures:load`) :

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@nextride.fr | Admin1234! |
| Client | client@nextride.fr | Client1234! |

Pour tester le paiement, utiliser une carte de test Stripe, par exemple `4242 4242 4242 4242`
(date future, CVC quelconque).

## Lancer les tests

Les tests s'exécutent contre une base de données dédiée (accessible via le conteneur `database`
du `docker-compose.yml`) :

```bash
docker exec -it nextride_app php bin/phpunit
```

## Intégration continue

Une pipeline GitHub Actions (`.github/workflows/ci.yml`) exécute automatiquement les tests
PHPUnit et le build de l'image Docker à chaque push.

## Structure du projet

```
src/
  Controller/    Contrôleurs (véhicules, réservations, paiement, admin, sécurité...)
  Entity/        Entités Doctrine (Vehicule, Reservation, Utilisateur)
  DataFixtures/  Jeux de données de démonstration
templates/       Vues Twig
tests/           Tests unitaires et fonctionnels PHPUnit
migrations/      Migrations Doctrine
```
