# Jalon 4 — Conception de l'application & Architecture
## NextRide — Plateforme de location de véhicules

> **Formation :** Concepteur Développeur d'Applications (CDA) — IPSSI Lille
> **Auteur :** Azeddine AMARI
> **Date :** 2026
> **Version :** 1.0
>
> Diagrammes générés à partir du code source réel (routes, contrôleurs, entités), pour une
> conformité totale entre la conception documentée et l'implémentation livrée.

---

## Sommaire

1. [Diagramme de cas d'utilisation](#1-diagramme-de-cas-dutilisation)
2. [Diagrammes de séquence](#2-diagrammes-de-séquence)
3. [Diagramme de classes](#3-diagramme-de-classes)
4. [Architecture multi-couches](#4-architecture-multi-couches)

---

## 1. Diagramme de cas d'utilisation

Trois acteurs interagissent avec le système : le **Visiteur** (non authentifié), le **Client**
(rôle `ROLE_USER`) et l'**Administrateur** (rôle `ROLE_ADMIN`). Dans l'implémentation, un compte
Administrateur possède toujours également `ROLE_USER` (voir `Utilisateur::getRoles()`), il hérite
donc de tous les cas d'utilisation du Client.

```mermaid
flowchart LR
    Visiteur(["🧑 Visiteur"])
    Client(["🧑‍💼 Client"])
    Admin(["🛡️ Administrateur"])

    subgraph SYS["Système NextRide"]
        UC1(("Consulter le catalogue"))
        UC2(("Filtrer / rechercher un véhicule"))
        UC3(("Voir la fiche d'un véhicule"))
        UC4(("Créer un compte"))
        UC5(("Se connecter / se déconnecter"))
        UC6(("Réserver un véhicule"))
        UC7(("Annuler une réservation"))
        UC8(("Consulter l'historique des réservations"))
        UC9(("Payer une réservation - Stripe"))
        UC10(("Consulter le reçu"))
        UC11(("Gérer les véhicules - CRUD"))
        UC12(("Gérer les réservations - annulation admin"))
        UC13(("Gérer les utilisateurs - suppression"))
    end

    Visiteur --> UC1
    Visiteur --> UC2
    Visiteur --> UC3
    Visiteur --> UC4
    Visiteur --> UC5

    Client --> UC1
    Client --> UC2
    Client --> UC3
    Client --> UC5
    Client --> UC6
    Client --> UC7
    Client --> UC8
    Client --> UC9
    Client --> UC10

    UC9 -.include.-> UC6
    UC10 -.include.-> UC9

    Admin --> UC11
    Admin --> UC12
    Admin --> UC13
    Admin -. hérite des cas Client .-> Client
```

**Notes de lecture :**
- `Payer une réservation` **inclut** `Réserver un véhicule` : on ne peut payer que ce qui a
  d'abord été réservé (`PaiementController` exige une `Reservation` existante).
- `Consulter le reçu` **inclut** `Payer une réservation` : le contrôleur
  (`ReservationController::recu`) redirige si `reservation.paye === false`.
- La gestion des véhicules, réservations et utilisateurs est **exclusivement** réservée à
  `ROLE_ADMIN`, contrôlée par l'attribut `#[IsGranted('ROLE_ADMIN')]` sur les contrôleurs
  `VehiculeController` (partiellement), `AdminReservationController` et
  `AdminUtilisateurController`.

## 2. Diagrammes de séquence

### 2.1 Authentification (connexion)

```mermaid
sequenceDiagram
    actor U as Utilisateur
    participant NAV as Navigateur
    participant SEC as Firewall Symfony (form_login)
    participant PROV as UserProvider
    participant DB as Base de données
    participant HASH as PasswordHasher

    U->>NAV: Saisit email + mot de passe
    NAV->>SEC: POST /login (email, password, csrf_token)
    SEC->>SEC: Vérifie le token CSRF
    SEC->>PROV: loadUserByIdentifier(email)
    PROV->>DB: SELECT * FROM utilisateur WHERE email = ?
    DB-->>PROV: Utilisateur (password haché)
    PROV-->>SEC: Utilisateur
    SEC->>HASH: isPasswordValid(utilisateur, password)
    HASH-->>SEC: true / false
    alt Identifiants valides
        SEC->>SEC: Crée le token de session authentifié
        SEC-->>NAV: 302 redirect vers app_home
    else Identifiants invalides
        SEC-->>NAV: 302 redirect vers /login (erreur)
    end
```

### 2.2 Réservation d'un véhicule

```mermaid
sequenceDiagram
    actor C as Client
    participant NAV as Navigateur
    participant RC as ReservationController
    participant RR as ReservationRepository
    participant RES as Reservation (entité)
    participant EM as EntityManager (Doctrine)
    participant DB as Base de données

    C->>NAV: Choisit dates début/fin, valide le formulaire
    NAV->>RC: POST /reservation/nouvelle/{id}
    RC->>RC: Vérifie vehicule.disponible
    alt Véhicule indisponible
        RC-->>NAV: Flash erreur + redirect fiche véhicule
    end
    RC->>RES: reservation.estPeriodeValide()
    alt dateFin <= dateDebut
        RES-->>RC: false
        RC-->>NAV: 200 + erreur "date de fin invalide"
    end
    RC->>RR: hasOverlappingReservation(vehicule, dateDebut, dateFin)
    RR->>DB: SELECT COUNT(*) ... WHERE statut = EN_COURS AND chevauchement
    DB-->>RR: count
    RR-->>RC: bool
    alt Chevauchement détecté
        RC-->>NAV: 200 + erreur "déjà réservé sur cette période"
    else Période libre
        RC->>RES: setUtilisateur(client), setStatut(EN_COURS)
        RC->>EM: persist(reservation) + flush()
        EM->>DB: INSERT INTO reservation (...)
        RC-->>NAV: 303 redirect vers /reservation (liste)
    end
```

### 2.3 Paiement d'une réservation (Stripe Checkout)

```mermaid
sequenceDiagram
    actor C as Client
    participant NAV as Navigateur
    participant PC as PaiementController
    participant STRIPE as API Stripe
    participant EM as EntityManager (Doctrine)
    participant DB as Base de données

    C->>NAV: Clique "Payer"
    NAV->>PC: GET /reservation/{id}/payer
    PC->>PC: Vérifie que la réservation appartient au client
    PC->>PC: Calcule montant = nombreJours * prixJour
    PC->>STRIPE: Checkout\Session::create(montant, success_url, cancel_url)
    STRIPE-->>PC: session (url de paiement hébergée)
    PC-->>NAV: 302 redirect vers checkout.stripe.com

    NAV->>STRIPE: Formulaire de paiement (carte bancaire)
    STRIPE-->>NAV: 302 redirect vers success_url?session_id=...

    NAV->>PC: GET /reservation/{id}/payer/succes?session_id=...
    PC->>STRIPE: Session::retrieve(session_id)
    STRIPE-->>PC: session.payment_status
    alt payment_status == "paid"
        PC->>EM: reservation.setPaye(true) + flush()
        EM->>DB: UPDATE reservation SET paye = 1
        PC-->>NAV: redirect vers /reservation/{id}/recu
    else Paiement non confirmé
        PC-->>NAV: Flash erreur + redirect /reservation
    end
```

## 3. Diagramme de classes

Le diagramme se concentre sur les entités métier (voir aussi le MLD du Jalon 3) et leurs
méthodes significatives ; les classes du framework Symfony (Controller, AbstractController, etc.)
ne sont pas représentées, conformément aux recommandations du cahier des charges technique.

```mermaid
classDiagram
    class Utilisateur {
        -int id
        -string email
        -string[] roles
        -string password
        -string nom
        -string prenom
        -DateTimeImmutable dateInscription
        +getRoles() string[]
        +getUserIdentifier() string
        +addReservation(Reservation) static
        +removeReservation(Reservation) static
    }

    class Vehicule {
        -int id
        -string marque
        -string modele
        -CategorieVehicule categorie
        -float prixJour
        -bool disponible
        -string description
        -string image
        +isDisponible() bool
        +addReservation(Reservation) static
        +removeReservation(Reservation) static
    }

    class Reservation {
        -int id
        -DateTimeImmutable dateDebut
        -DateTimeImmutable dateFin
        -StatutReservation statut
        -bool paye
        +estPeriodeValide() bool
        +getNombreJours() int
        +getMontantTotal() float
        +isPaye() bool
    }

    class CategorieVehicule {
        <<enumeration>>
        CITADINE
        SUV
        LUXE_SPORTIVE
    }

    class StatutReservation {
        <<enumeration>>
        EN_COURS
        ANNULEE
        TERMINEE
    }

    Utilisateur "1" --> "0..*" Reservation : effectue
    Vehicule "1" --> "0..*" Reservation : concerne
    Vehicule --> CategorieVehicule : categorie
    Reservation --> StatutReservation : statut
```

**Repositories associés** (couche d'accès aux données, un par entité, héritant de
`ServiceEntityRepository` de Doctrine) : `UtilisateurRepository`, `VehiculeRepository`,
`ReservationRepository`. Ce dernier porte la logique métier de détection de chevauchement de
réservations (`hasOverlappingReservation`), volontairement placée dans le repository plutôt que
dans le contrôleur pour respecter la séparation des responsabilités.

## 4. Architecture multi-couches

### Pattern MVC

NextRide est une application **full-stack Symfony** (choix justifié au Jalon 1) :

- **Contrôleurs** (`src/Controller/`) : reçoivent les requêtes HTTP, orchestrent la logique via
  les repositories et l'`EntityManager`, et retournent soit une vue Twig, soit une redirection.
  Un contrôleur par domaine fonctionnel : `HomeController`, `VehiculeController`,
  `ReservationController`, `PaiementController`, `RegistrationController`, `SecurityController`,
  `AdminReservationController`, `AdminUtilisateurController`, `LegalController`.
- **Modèle** : les entités Doctrine (`src/Entity/`) portent les données *et* une partie de la
  logique métier pure (ex : `Reservation::getMontantTotal()`, `estPeriodeValide()`), tandis que
  les repositories (`src/Repository/`) encapsulent les requêtes complexes (ex : détection de
  chevauchement de dates).
- **Vues** : templates Twig (`templates/`), organisés par domaine fonctionnel, héritant d'un
  `base.html.twig` commun.

### Architecture n-tiers (déploiement)

```mermaid
flowchart LR
    subgraph Client["Poste client"]
        Navigateur
    end
    subgraph Serveur["Conteneur Docker 'app'"]
        Apache --> PHP["PHP-Apache 8.2 + Symfony 6.4"]
    end
    subgraph BDD["Conteneur Docker 'database'"]
        MySQL[(MySQL 8.0)]
    end
    subgraph Externe["Service externe"]
        Stripe[API Stripe]
    end

    Navigateur -- HTTP :8080 --> Apache
    PHP -- Doctrine DBAL :3306 --> MySQL
    PHP -- HTTPS API --> Stripe
    Stripe -- Checkout hébergé --> Navigateur
```

Chaque couche logique tourne dans un conteneur Docker distinct, orchestré par
`docker-compose.yml` (`app`, `database`, `adminer` pour l'administration BDD, `mailer` pour les
emails en développement via Mailpit). Le déploiement en production reprendrait la même
architecture n-tiers logique, les trois tiers (client, application, données) pouvant être répartis
sur des hôtes physiques différents sans changement de code.

### Séparation des responsabilités et bonnes pratiques

- **Principe de responsabilité unique** : chaque contrôleur ne gère qu'un domaine fonctionnel ;
  la logique de disponibilité des véhicules est isolée dans `ReservationRepository`, pas dupliquée
  dans les contrôleurs.
- **Sécurité par attributs** : le contrôle d'accès est déclaré directement sur les contrôleurs via
  `#[IsGranted('ROLE_ADMIN')]` / `#[IsGranted('ROLE_USER')]`, plutôt que dispersé dans la logique
  métier.
- **Configuration sensible externalisée** : la clé secrète Stripe est injectée via variable
  d'environnement (`STRIPE_SECRET_KEY`, définie dans `.env.local`, non versionnée), jamais codée
  en dur — voir `PaiementController::__construct(string $stripeSecretKey)`.
- **Validation des entrées** : les formulaires Symfony (`ReservationType`, `RegistrationForm`)
  appliquent les contraintes de validation avant toute écriture en base.

### Composants externes

| Bibliothèque | Rôle |
|---|---|
| `stripe/stripe-php` | SDK officiel Stripe, création de sessions Checkout et vérification du statut de paiement |
| `doctrine/orm` + `doctrine/doctrine-bundle` | ORM, mapping entités ↔ tables MySQL |
| `symfony/security-bundle` | Authentification, hachage des mots de passe, contrôle d'accès par rôles |
| `flatpickr` (JS, via Stimulus) | Calendrier de sélection de dates avec périodes indisponibles désactivées |
| `symfony/mailer` + Mailpit (dev) | Infrastructure d'envoi d'emails (captée localement en développement) |
