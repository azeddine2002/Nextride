# Jalon 2 — Méthodologie & Gestion de Projet
## NextRide — Plateforme de location de véhicules

---

> **Formation :** Concepteur Développeur d'Applications (CDA) — IPSSI Lille
> **Auteur :** Azeddine AMARI
> **Date :** 2026
> **Version :** 1.0

---

## Sommaire

1. [Méthode de gestion de projet](#1-méthode-de-gestion-de-projet)
2. [Planning macro du projet](#2-planning-macro-du-projet)
3. [Outils de suivi](#3-outils-de-suivi)
4. [Gestion du code source (Git)](#4-gestion-du-code-source-git)
5. [Plan CI/CD](#5-plan-cicd)

---

## 1. Méthode de gestion de projet

### Méthode retenue : Kanban

Pour ce projet individuel, j'ai choisi la méthode **Kanban**. Ce choix est justifié par plusieurs raisons :

- Le projet est mené **en solo**, sans équipe à coordonner
- Kanban permet une **visualisation claire** des tâches à tout moment
- Il est **flexible** : on peut ajouter ou réorganiser les tâches sans cérémonie particulière
- Il correspond bien à un travail en **livraisons mensuelles** (jalons)

Contrairement à Scrum qui nécessite des sprints fixes et des réunions régulières, Kanban s'adapte mieux au rythme d'un étudiant travaillant seul sur plusieurs mois.

### Organisation du tableau Kanban

Le tableau est divisé en **4 colonnes** :

| À faire | En cours | En révision | Terminé |
|---|---|---|---|
| Tâches planifiées | Tâche(s) en cours de développement | À relire / corriger | Validé et livré |

> Outil utilisé : **GitHub Projects** (intégré au dépôt GitHub du projet)

---

## 2. Planning macro du projet

Le projet est découpé en **6 jalons mensuels**. Voici le planning global :

| Jalon | Mois | Objectif principal | Livrables |
|---|---|---|---|
| **Jalon 1** | Janvier | Cadrage fonctionnel | Cahier des charges fonctionnel (PDF) |
| **Jalon 2** | Février | Organisation & Design | Doc méthodologie + Maquettes UI/UX |
| **Jalon 3** | Mars | Modélisation des données | MCD, MLD, MPD, dictionnaire des données |
| **Jalon 4** | Avril | Conception technique | Diagrammes UML, architecture |
| **Jalon 5** | Mai | Développement bêta | Code source + tests + sécurité |
| **Jalon 6** | Juin | Mise en production | Version finale + documentation complète |

### Découpage des tâches par jalon

**Jalon 2 — Février**
- Choix de la méthode de gestion de projet
- Création du dépôt GitHub et configuration des branches
- Réalisation du zoning et des wireframes
- Définition de la charte graphique
- Création des maquettes haute fidélité (desktop + mobile)

**Jalon 3 — Mars**
- Identification des entités métier
- Rédaction du dictionnaire des données
- Réalisation du MCD
- Traduction en MLD puis MPD
- Création de la base de données MySQL

**Jalon 4 — Avril**
- Diagrammes de cas d'utilisation (Use Cases)
- Diagrammes de séquence (2 à 3 scénarios clés)
- Diagramme de classes
- Description de l'architecture MVC + n-tiers
- Initialisation du projet Symfony

**Jalon 5 — Mai**
- Développement des fonctionnalités principales
- Intégration de l'API externe de paiement (Stripe)
- Mise en place des tests unitaires et fonctionnels
- Sécurisation de l'application (OWASP)
- Configuration de la pipeline CI

**Jalon 6 — Juin**
- Corrections et finalisation du code
- Dockerisation complète
- Rédaction de la documentation finale
- Préparation de la soutenance

---

## 3. Outils de suivi

| Outil | Usage |
|---|---|
| **GitHub Projects** | Tableau Kanban de suivi des tâches |
| **GitHub Issues** | Traçabilité des fonctionnalités et des bugs |
| **Git / GitHub** | Versioning du code source |
| **Notion** | Documentation et livrables |

### Fréquence de mise à jour

- Le tableau Kanban est mis à jour **à chaque session de travail**
- Un bilan rapide est fait **en fin de semaine** pour vérifier l'avancement
- Le planning est ajusté si nécessaire à chaque début de jalon

---

## 4. Gestion du code source (Git)

### Stratégie de branches

J'adopte une stratégie de branches inspirée de **GitFlow simplifié** :

```
main          → version stable (livrée à chaque jalon)
develop       → intégration des fonctionnalités en cours
feature/xxx   → une branche par fonctionnalité (ex: feature/auth, feature/reservation)
fix/xxx       → corrections de bugs
```

### Règles de commit

- Messages de commit **clairs et descriptifs** : `feat: ajout du formulaire de réservation`
- Préfixes utilisés :
  - `feat:` → nouvelle fonctionnalité
  - `fix:` → correction de bug
  - `docs:` → documentation
  - `test:` → ajout de tests
  - `chore:` → configuration, Docker, CI

### Exemple de workflow

1. Créer une branche `feature/gestion-vehicules` depuis `develop`
2. Développer la fonctionnalité
3. Pousser la branche et créer une Pull Request vers `develop`
4. Relire le code, corriger si besoin
5. Merger dans `develop`
6. En fin de jalon : merger `develop` dans `main` avec un tag de version (ex: `v0.2.0`)

> **Dépôt GitHub :** https://github.com/azeddine2002/Nextride

---

## 5. Plan CI/CD

### Intégration Continue (CI)

La pipeline CI est configurée via **GitHub Actions**. Elle se déclenche automatiquement à chaque `push` sur les branches `develop` et `main`.

**Étapes mises en œuvre :**

```yaml
# Déclenchement : push sur develop ou main
1. Installation des dépendances PHP (composer install)
2. Exécution des tests unitaires (PHPUnit)
3. Exécution des tests fonctionnels
4. Build de l'image Docker (vérification que le build passe)
```

### Déploiement Continu (CD)

Dans un premier temps, le déploiement continu est **manuel** :

1. La pipeline CI valide le code sur `develop`
2. Merge manuel dans `main` après validation
3. Déploiement via `docker-compose up -d` sur le serveur cible

**Évolution prévue (jalon 6) :** automatisation du déploiement de l'image Docker sur DockerHub à chaque release taggée sur `main`.

### Environnements

| Environnement | Description |
|---|---|
| **dev** | Local, Docker Compose, `APP_ENV=dev` |
| **prod** | Serveur distant, `APP_ENV=prod`, debug désactivé |

---

*Document rédigé dans le cadre du Jalon 2 — Formation CDA — IPSSI Lille — 2026*
