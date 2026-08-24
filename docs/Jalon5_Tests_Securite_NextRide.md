# Jalon 5 — Développement, Sécurité & Tests (version Bêta)
## NextRide — Plateforme de location de véhicules

> **Formation :** Concepteur Développeur d'Applications (CDA) — IPSSI Lille
> **Auteur :** Azeddine AMARI
> **Date :** 2026
> **Version :** 1.0
>
> Ce rapport reflète l'état réel du projet à la date de rédaction : chaque chiffre et chaque
> constat proviennent d'une exécution effective (tests lancés, pipeline CI consultée, code relu),
> pas d'une estimation.

---

## Sommaire

1. [Code source et déployabilité](#1-code-source-et-déployabilité)
2. [Intégration de l'API externe (Stripe)](#2-intégration-de-lapi-externe-stripe)
3. [Preuve de mise en place de la CI](#3-preuve-de-mise-en-place-de-la-ci)
4. [Rapport de tests automatisés](#4-rapport-de-tests-automatisés)
5. [Analyse de sécurité & conformité](#5-analyse-de-sécurité--conformité)
6. [Bilan d'avancement](#6-bilan-davancement)

---

## 1. Code source et déployabilité

- **Dépôt Git :** https://github.com/azeddine2002/Nextride (branche `develop`)
- L'application est **déployable et testable** via un simple `docker-compose up -d --build`
  suivi des commandes Doctrine (base, migrations, fixtures) — procédure détaillée dans le
  [README](../README.md).
- **Fonctionnalités principales implémentées et vérifiées manuellement de bout en bout** :
  inscription/connexion, catalogue avec recherche et filtres, fiche véhicule, réservation avec
  calendrier de disponibilité (dates déjà prises désactivées), paiement Stripe, reçu de
  réservation, annulation de réservation, espace admin (véhicules, réservations, utilisateurs).
- Aucune fonctionnalité principale du CDCF n'est manquante à ce stade, à une exception près :
  le **droit de suppression de compte** promis dans le CDCF (§6, RGPD) n'est aujourd'hui
  exerçable que **par l'administrateur** (`AdminUtilisateurController::supprimer`), pas en
  self-service par le client lui-même. Point à corriger ou à requalifier avant la documentation
  finale du Jalon 6.

## 2. Intégration de l'API externe (Stripe)

L'API externe intégrée est **Stripe Checkout** (paiement en ligne), conformément au choix arrêté
dans le CDCF.

- Clé secrète injectée par variable d'environnement (`STRIPE_SECRET_KEY` dans `.env.local`, non
  versionné), jamais codée en dur — voir `PaiementController`.
- Flux vérifié manuellement de bout en bout avec une carte de test Stripe
  (`4242 4242 4242 4242`) : création de session Checkout → redirection vers Stripe → paiement →
  callback de succès → mise à jour de `reservation.paye` → génération du reçu.
- Le montant transmis à Stripe est calculé côté serveur (`Reservation::getMontantTotal()`),
  jamais fourni par le client, ce qui évite toute manipulation du prix depuis le navigateur.

## 3. Preuve de mise en place de la CI

La pipeline **GitHub Actions** (`.github/workflows/ci.yml`) se déclenche à chaque `push` et
`pull_request`, avec deux jobs :

1. **`tests`** : installe les dépendances Composer, exécute les migrations sur une vraie base
   MySQL 8.0 de service, puis lance `php bin/phpunit`.
2. **`build-docker`** : construit l'image Docker de l'application (`docker build`), pour
   s'assurer que le projet reste packageable en continu.

**Historique réel des exécutions** (consulté sur GitHub Actions) :

| Run | Commit | Durée | Statut |
|---|---|---|---|
| #7 | `feat: recu de reservation, calendrier de dispo et correctifs paiement` | 44s | ✅ |
| #6 | `fix: favicon personnalise, texte hero naturel et boutons catalogue` | 53s | ✅ |
| #5 | `merge: fixtures vehicules reels` | 54s | ✅ |
| #4 | `merge: upload photo vehicule` | 54s | ✅ |
| #3 | `merge: espace admin` | 54s | ✅ |
| #2 | `merge: integration UI/UX des maquettes` | 52s | ✅ |
| #1 | `merge: pipeline CI` | 53s | ✅ |

**7 exécutions consécutives réussies**, aucune régression détectée sur la branche `develop`.

> **À faire avant le Jalon 6 :** pousser les commits les plus récents (docs jalons 1-4, correctifs
> photos/perf Docker) pour déclencher une 8ᵉ exécution et prendre une capture d'écran à jour pour
> la documentation finale.

## 4. Rapport de tests automatisés

### Outils utilisés

- **PHPUnit 11.5** pour les tests unitaires et fonctionnels
- **DAMA Doctrine Test Bundle** : isole chaque test dans une transaction annulée à la fin,
  garantissant une base propre à chaque exécution sans recréer le schéma
- Une base de données MySQL dédiée aux tests (`nextride_test`), séparée de la base de
  développement

### Couverture

**26 tests, 58 assertions**, répartis ainsi :

| Fichier de test | Ce qui est couvert |
|---|---|
| `Entity/ReservationTest.php` (4 tests) | Logique métier pure : `estPeriodeValide()` — dates cohérentes, dates inversées, dates identiques, dates manquantes |
| `Controller/SecurityControllerTest.php` (2 tests) | Connexion avec identifiants valides / invalides |
| `Controller/VehiculeControllerTest.php` (6 tests) | Catalogue public, accès admin protégé (redirection anonyme, 403 non-admin), création de véhicule (avec et sans photo) |
| `Controller/ReservationControllerTest.php` (5 tests) | Réservation d'un véhicule disponible, **refus de réservation chevauchante** (même client et clients différents), refus si véhicule indisponible, accès anonyme redirigé |
| `Controller/AdminReservationControllerTest.php` (4 tests) | Contrôle d'accès (anonyme, non-admin), consultation et annulation des réservations par l'admin |
| `Controller/AdminUtilisateurControllerTest.php` (5 tests) | Contrôle d'accès, suppression d'un utilisateur, suppression en cascade de ses réservations, protection contre l'auto-suppression de l'admin |

**Exemple de cas de test unitaire pertinent** — vérification que le système refuse une réservation
chevauchant une réservation existante, même émise par un autre client
(`testReservationEnglobanteRefuseeAvecDeuxClientsDifferents`), garantissant qu'un véhicule ne peut
jamais être double-réservé.

### Tests fonctionnels

Les tests `Controller/*` sont des tests fonctionnels Symfony (`WebTestCase`) : ils simulent de
vraies requêtes HTTP (GET/POST, formulaires, redirections, codes de statut, contenu rendu) contre
le noyau applicatif complet, base de données comprise — pas de mocks sur la couche métier.

### Résultat actuel

```
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.33
Configuration: /var/www/html/phpunit.dist.xml

..........................                                        26 / 26 (100%)

Time: 00:29.767, Memory: 50.50 MB

OK (26 tests, 58 assertions)
```

**Tous les tests passent (100%, vert).** Aucun test connu en échec à ce stade.

### Autres observations (informel, hors suite automatisée)

Lors des tests manuels de bout en bout, les temps de réponse mesurés (curl, environnement Docker
local) étaient de l'ordre de **1 à 2 secondes par page** en mode développement (profiler Symfony
actif). Cela reste indicatif — aucun test de charge formalisé (JMeter ou équivalent) n'a été
réalisé, ce qui est cohérent avec le périmètre pédagogique du projet.

## 5. Analyse de sécurité & conformité

| Risque OWASP | Mesure en place | État |
|---|---|---|
| **Injection SQL** | Toutes les requêtes passent par Doctrine ORM / QueryBuilder (`ReservationRepository::hasOverlappingReservation`, etc.). Aucune requête SQL brute concaténée avec une entrée utilisateur n'a été trouvée dans `src/`. | ✅ |
| **XSS** | Twig échappe les sorties par défaut ; aucun filtre `\|raw` n'est utilisé dans les templates (vérifié). | ✅ |
| **CSRF** | Jetons CSRF actifs sur le formulaire de connexion (`enable_csrf: true`) et sur l'annulation de réservation (`isCsrfTokenValid('annuler'.id, ...)`). | ✅ |
| **Mots de passe** | Hachage via `UserPasswordHasherInterface` de Symfony (algorithme `auto`, bcrypt/Argon2 selon la plateforme) ; jamais stocké en clair. | ✅ |
| **Politique de mot de passe** | Longueur minimale de 6 caractères imposée à l'inscription (`RegistrationFormType`). | ⚠️ Fonctionnel mais faible : 8+ caractères serait recommandé |
| **Format email** | Aucune contrainte `Assert\Email` explicite sur le champ email de l'entité `Utilisateur` ; seule l'unicité est vérifiée. | ⚠️ À ajouter |
| **Brute force / limitation de connexion** | Aucun throttling des tentatives de connexion en place. | ❌ Non fait (amélioration possible, non bloquante) |
| **Contrôle d'accès** | Attributs `#[IsGranted('ROLE_ADMIN')]` / `#[IsGranted('ROLE_USER')]` sur les contrôleurs sensibles ; vérifié manuellement (client → 403 sur toutes les routes `/admin/*` et `/vehicule` en écriture). | ✅ |
| **RGPD — droit d'accès/rectification** | Géré via les formulaires existants (profil visible dans "Mes réservations"). | ✅ |
| **RGPD — droit de suppression** | Implémenté **côté admin uniquement**, pas en self-service côté client. | ⚠️ Écart avec le CDCF, à corriger ou requalifier |
| **RGPD — mentions légales / confidentialité** | Pages dédiées (`/mentions-legales`, `/confidentialite`) présentes. | ✅ |
| **HTTPS** | Non applicable en développement local (HTTP). À activer en production. | ➖ Hors périmètre dev |
| **En-têtes de sécurité (CORS, X-Frame-Options)** | Non configurés spécifiquement. | ❌ Non fait (amélioration possible) |
| **Secrets** | Clé Stripe et `APP_SECRET` externalisés via variables d'environnement, `.env.local` non versionné (vérifié dans `.gitignore` et l'historique Git). | ✅ |

### Synthèse

Les protections **structurelles** (injection SQL, XSS, CSRF, hachage des mots de passe, contrôle
d'accès par rôle) sont **en place et vérifiées**. Les points restants sont des **améliorations de
durcissement** (limitation de connexion, en-têtes de sécurité, politique de mot de passe, format
email) courantes à ce stade d'un projet pédagogique, et un **écart fonctionnel RGPD identifié**
(suppression de compte non self-service) à traiter avant la documentation finale.

## 6. Bilan d'avancement

**Terminé :**
- Toutes les fonctionnalités principales du CDCF (sauf nuance ci-dessous)
- Intégration Stripe opérationnelle et testée
- Pipeline CI fonctionnelle (7/7 exécutions vertes)
- Suite de tests automatisés : 26/26 tests verts, couvrant les règles métier critiques
  (non-chevauchement des réservations, contrôle d'accès admin)
- Protections de sécurité structurelles (SQLi, XSS, CSRF, hachage mots de passe)

**Restant pour le Jalon 6 :**
- Corriger ou requalifier l'écart RGPD (suppression de compte)
- Durcissements de sécurité optionnels (throttling login, en-têtes HTTP, contrainte email,
  longueur de mot de passe)
- Tag de release Git (`v1.0`)
- Documentation finale consolidée + guide utilisateur
- Pousser les derniers commits et vérifier la 8ᵉ exécution CI

Le projet est en avance sur la partie technique par rapport au calendrier des jalons ; l'effort
restant est concentré sur la consolidation documentaire et deux ajustements de sécurité mineurs.
