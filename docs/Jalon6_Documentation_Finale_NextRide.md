# Jalon 6 — Documentation finale & Mise en production
## NextRide — Plateforme de location de véhicules

> **Formation :** Concepteur Développeur d'Applications (CDA) — IPSSI Lille
> **Auteur :** Azeddine AMARI
> **Date :** 2026
> **Version :** 1.0 — livrable final
> **Dépôt :** https://github.com/azeddine2002/Nextride (branche `develop`, tag `v1.0`)

---

Ce document consolide l'ensemble des livrables produits durant les 6 jalons du projet fil rouge.
Chaque chapitre renvoie vers le document détaillé correspondant plutôt que de le dupliquer, et
précise ce qui a évolué depuis sa version initiale.

## Sommaire

- [III. Cahier des charges](#iii-cahier-des-charges)
- [IV. Méthodologie et organisation](#iv-méthodologie-et-organisation)
- [V. Conception UI/UX](#v-conception-uiux)
- [VI. Modélisation de la base de données](#vi-modélisation-de-la-base-de-données)
- [VII. Conception de l'application (UML)](#vii-conception-de-lapplication-uml)
- [VIII. Architecture multi-couches](#viii-architecture-multi-couches)
- [IX. Sécurité](#ix-sécurité)
- [X. Tests](#x-tests)
- [XI. Déploiement et mise en production](#xi-déploiement-et-mise-en-production)
- [Guide utilisateur](#guide-utilisateur)
- [Conclusion et perspectives](#conclusion-et-perspectives)

---

## III. Cahier des charges

Voir [Jalon1_CDCF_NextRide.md](Jalon1_CDCF_NextRide.md) pour le document complet.

**Mise à jour par rapport à la version initiale :** le périmètre fonctionnel livré correspond au
CDCF initial, à une nuance près sur le droit de suppression de compte RGPD — voir
[chapitre IX. Sécurité](#ix-sécurité) pour le détail et la justification.

## IV. Méthodologie et organisation

Voir [Jalon2_Methodologie_NextRide.md](Jalon2_Methodologie_NextRide.md) pour la méthode de gestion
de projet (Kanban), le planning macro et la stratégie Git détaillés.

**Planning réel vs prévisionnel :** le développement effectif du code (fonctionnalités, tests, CI,
Docker) a été mené de façon intensive sur une période resserrée plutôt qu'étalé mensuellement
comme prévu au planning initial ; en revanche, la documentation de suivi (jalons 1 à 6) a été
consolidée après coup, en s'appuyant sur le code déjà fonctionnel pour garantir sa cohérence avec
l'implémentation réelle plutôt que de documenter des intentions non vérifiées.

**Retour d'expérience méthode :** travailler seul avec Kanban (sans cérémonies imposées) a permis
de rester concentré sur la production de code fonctionnel ; la contrepartie a été un effort de
rattrapage documentaire plus lourd en fin de parcours — enseignement à appliquer sur un prochain
projet : documenter au fil de l'eau, jalon par jalon, plutôt qu'en différé.

## V. Conception UI/UX

Voir [Jalon2_Methodologie_NextRide.md](Jalon2_Methodologie_NextRide.md) et le dossier
[jalon2-uiux/](jalon2-uiux/) (zoning, wireframes, charte graphique, maquettes HD desktop/mobile).

**Conformité maquettes → produit final :** l'interface réellement livrée (voir captures dans le
[guide utilisateur](#guide-utilisateur) ci-dessous) reprend la charte graphique définie
(bleu marine / orange, typographie Inter), la structure catalogue + filtres de la maquette
desktop, et s'adapte correctement au mobile.

## VI. Modélisation de la base de données

Voir [Jalon3_Modelisation_BD_NextRide.md](Jalon3_Modelisation_BD_NextRide.md) pour le dictionnaire
de données, le MCD, le MLD et le MPD complets.

**État final :** le schéma livré correspond exactement au MPD documenté (3 tables métier :
`utilisateur`, `vehicule`, `reservation`, plus la table technique `messenger_messages`). Le point
d'attention relevé (clés étrangères `reservation.utilisateur_id` / `vehicule_id` nullables alors
que la règle de gestion impose une cardinalité `(1,1)`) reste ouvert — voir
[Perspectives](#conclusion-et-perspectives).

## VII. Conception de l'application (UML)

Voir [Jalon4_Conception_UML_NextRide.md](Jalon4_Conception_UML_NextRide.md) pour les diagrammes de
cas d'utilisation, de séquence (authentification, réservation, paiement) et de classes.

**Fidélité au code :** ces diagrammes ont été produits directement à partir du code source final
(routes réelles via `debug:router`, signatures de contrôleurs, entités Doctrine) : ils reflètent
l'implémentation livrée sans écart.

## VIII. Architecture multi-couches

Détaillée dans le [chapitre 4 du Jalon 4](Jalon4_Conception_UML_NextRide.md#4-architecture-multi-couches).
En synthèse : application full-stack Symfony 6.4 (MVC), architecture n-tiers déployée via 3
conteneurs Docker (`app` Apache/PHP 8.2, `database` MySQL 8.0, `mailer` Mailpit pour le dev),
consommant l'API externe Stripe pour le paiement.

**Évolution technique notable post-conception initiale :** en cours de test, un problème de
performance a été identifié et corrigé — le montage complet du code source depuis le système de
fichiers Windows vers le conteneur (bind mount) ralentissait chaque requête HTTP à plus de 20
secondes. Correction : `vendor/` et `var/` (cache) ont été isolés dans des volumes Docker natifs,
ramenant le temps de réponse à ~1-2 secondes et le temps d'exécution de la suite de tests de 6
minutes à 30 secondes. Détails dans l'historique Git (commit `perf: isole vendor/ et var/ du bind
mount Windows`).

## IX. Sécurité

Voir [Jalon5_Tests_Securite_NextRide.md](Jalon5_Tests_Securite_NextRide.md) pour l'analyse complète
point par point (OWASP, RGPD).

**Résumé final :** les protections structurelles (injection SQL via ORM, XSS via échappement Twig,
CSRF, hachage des mots de passe, contrôle d'accès par rôle) sont en place et vérifiées
manuellement (tests d'accès 403/redirection). Deux écarts restent identifiés et assumés dans cette
version 1.0 :

1. Le **droit de suppression de compte RGPD** n'est aujourd'hui exerçable que par
   l'administrateur, pas en self-service par le client.
2. Des **durcissements optionnels** ne sont pas implémentés : limitation des tentatives de
   connexion (brute-force), contrainte de format sur l'email, longueur de mot de passe portée à
   8+ caractères, en-têtes de sécurité HTTP (CORS, X-Frame-Options).

Ces deux points sont documentés comme axes d'amélioration plutôt que masqués, conformément à
l'exigence d'honnêteté du rapport de tests (Jalon 5).

## X. Tests

Voir [Jalon5_Tests_Securite_NextRide.md](Jalon5_Tests_Securite_NextRide.md) pour le détail complet.

**Bilan final :** 26 tests automatisés (unitaires + fonctionnels), 58 assertions, **100% de
réussite**. Pipeline CI GitHub Actions active sur `develop`, 7 exécutions historiques toutes
vertes. Aucune régression connue au moment de la livraison.

## XI. Déploiement et mise en production

### Environnements

| Environnement | Configuration |
|---|---|
| **dev** (local) | `APP_ENV=dev`, Docker Compose, profiler Symfony actif, données de démonstration via fixtures |
| **test** (CI) | `APP_ENV=test`, base MySQL de service dédiée, exécutée à chaque push GitHub Actions |
| **prod** (cible) | `APP_ENV=prod` (à définir dans les variables d'environnement du serveur cible), debug désactivé, HTTPS obligatoire, secrets via variables d'environnement (jamais commités) |

### Procédure de déploiement (manuelle, pédagogique)

1. Sur le serveur cible : cloner le dépôt (tag `v1.0`) et copier `docker-compose.yml` +
   `Dockerfile`.
2. Définir les variables d'environnement de production (`APP_ENV=prod`, `APP_SECRET`,
   `DATABASE_URL`, `STRIPE_SECRET_KEY` — clé Stripe **live**, distincte de la clé de test) dans un
   `.env.local` non versionné sur le serveur.
3. `docker-compose up -d --build`
4. `docker exec <conteneur_app> php bin/console doctrine:migrations:migrate --no-interaction --env=prod`
5. `docker exec <conteneur_app> php bin/console cache:clear --env=prod`
6. Vérifier l'accès HTTPS (reverse proxy / certificat TLS en amont du conteneur `app`, non
   fourni dans ce dépôt pédagogique).

### Stratégie de mise en production envisagée

Pour une mise à jour ultérieure sans interruption de service, la stratégie recommandée serait un
déploiement **blue/green** : construire la nouvelle image Docker en parallèle de la version en
production, exécuter les migrations sur une base compatible avec les deux versions, puis basculer
le routage (reverse proxy) vers les nouveaux conteneurs une fois leur bon fonctionnement vérifié,
avant d'arrêter les anciens. Dans le cadre pédagogique de ce projet, un déploiement manuel pendant
une fenêtre de maintenance est suffisant et a été retenu comme hypothèse de travail.

### Déploiement continu (CD)

Non implémenté à ce jour : la CI (tests + build Docker) est automatisée, mais le déploiement reste
manuel. Évolution possible : ajouter un job GitHub Actions poussant l'image construite vers un
registre (Docker Hub / GitHub Container Registry) sur les tags `v*`, avec un script de pull sur le
serveur cible.

## Guide utilisateur

### Comptes de démonstration

| Rôle | Email | Mot de passe |
|---|---|---|
| Client | client@nextride.fr | Client1234! |
| Administrateur | admin@nextride.fr | Admin1234! |

### Parcours client

1. Se rendre sur la page d'accueil : catalogue de véhicules avec recherche et filtres
   (catégorie, prix maximum).
2. Cliquer sur un véhicule pour voir sa fiche détaillée, ou directement sur "Réserver".
3. Se connecter (ou créer un compte via "Connexion" → lien d'inscription).
4. Choisir une date de début et une date de fin dans le calendrier (les dates déjà réservées sont
   désactivées), puis confirmer.
5. Depuis "Mes réservations", cliquer sur "Payer" : redirection vers Stripe Checkout. Utiliser la
   carte de test `4242 4242 4242 4242`, une date d'expiration future et un CVC à 3 chiffres.
6. Après paiement, un reçu de réservation est disponible.
7. Une réservation peut être annulée depuis "Mes réservations" tant qu'elle n'est pas payée ou
   selon les règles métier en vigueur.

### Parcours administrateur

1. Se connecter avec le compte administrateur.
2. Menu "Véhicules" : ajouter, modifier (y compris la photo), supprimer un véhicule.
3. Section réservations admin : consulter toutes les réservations de la plateforme, en annuler
   une si besoin.
4. Section utilisateurs admin : consulter la liste des comptes, supprimer un compte si nécessaire
   (ses réservations sont supprimées en cascade).

## Conclusion et perspectives

### Bilan

Le projet NextRide répond à l'objectif initial : digitaliser la réservation de véhicules de
location avec une plateforme centralisée, en remplacement d'une gestion manuelle par téléphone/
email. L'ensemble des fonctionnalités principales du CDCF est implémenté et testé, la sécurité
applicative de base est en place, la chaîne CI garantit l'absence de régression à chaque commit,
et le projet est entièrement conteneurisé et reproductible.

### Défis rencontrés

- **Performance Docker sur Windows** : diagnostiqué et corrigé (isolation de `vendor/`/`var/` en
  volumes natifs), avec un gain de temps de réponse d'environ 90%.
- **Cohérence des données de démonstration** : les fixtures ne restauraient pas les photos des
  véhicules après un rechargement de la base ; corrigé en les rattachant explicitement dans
  `AppFixtures.php` et en versionnant les images de démonstration.
- **Suivi documentaire jalon par jalon** : effort de rattrapage nécessaire en fin de parcours pour
  produire les livrables de conception (BD, UML) a posteriori — réalisable ici uniquement parce
  que le code source faisait autorité et était directement consultable.

### Améliorations envisageables

1. Ajouter la suppression de compte en self-service côté client (actuellement admin uniquement).
2. Rendre `reservation.utilisateur_id` et `reservation.vehicule_id` non-nullables en base, pour
   aligner le MPD sur la règle de gestion `(1,1)` du MCD.
3. Durcissements de sécurité : limitation des tentatives de connexion, contrainte de format email,
   mot de passe à 8+ caractères, en-têtes de sécurité HTTP.
4. Déploiement continu (CD) : automatiser la publication de l'image Docker vers un registre à
   chaque tag de release.
5. Notifications email (confirmation de réservation, reçu) via `symfony/mailer`, l'infrastructure
   (Mailpit en dev) étant déjà en place mais non exploitée fonctionnellement.
6. Tests de performance formalisés (JMeter ou k6) pour objectiver le comportement sous charge.
