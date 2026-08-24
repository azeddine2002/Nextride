# Cahier des charges fonctionnel — NextRide

**Formation :** Concepteur Développeur d'Applications (CDA)
**Établissement :** IPSSI Lille
**Réalisé par :** Azeddine Amari
**Année :** 2026

---

## Sommaire

1. Présentation du projet
2. Description fonctionnelle
3. Besoins non fonctionnels
4. Exigences techniques
5. Contraintes du projet
6. Données personnelles (RGPD)
7. Risques identifiés
8. Critères de réussite

---

## 1. Présentation du projet

### 1.1 Contexte et commanditaire

Le projet **NextRide** est une application web de gestion et de réservation de véhicules de
location.

Le commanditaire est une entreprise fictive spécialisée dans la location de véhicules,
souhaitant développer une solution numérique afin de moderniser son système de gestion.

Ce projet est réalisé dans un cadre pédagogique de formation Concepteur Développeur
d'Applications (CDA).

Le commanditaire du projet est un service fictif de location de voitures nommé **NextRide**,
souhaitant digitaliser son système de réservation afin de :

- réduire les échanges manuels (téléphone / emails),
- améliorer la gestion des véhicules disponibles,
- offrir une meilleure expérience client.

### 1.2 Problématique

Les systèmes de location de véhicules existants présentent plusieurs limites :

- gestion non centralisée des disponibilités,
- erreurs fréquentes de réservation,
- manque de visibilité en temps réel pour les clients,
- perte de temps dans le traitement administratif.

### 1.3 Objectifs du projet

Le projet NextRide vise à répondre à ces problématiques en proposant :

- permettre la réservation de véhicules en ligne,
- gérer les disponibilités en temps réel,
- centraliser les données clients et réservations,
- simplifier la gestion pour l'administrateur.

## 2. Description fonctionnelle

### 2.1 Acteurs du système

- Client
- Administrateur

### 2.2 Fonctionnalités côté client

- Création de compte / connexion
- Consultation des véhicules disponibles
- Filtrage des véhicules (type, prix, disponibilité)
- Réservation d'un véhicule
- Annulation d'une réservation
- Consultation de l'historique des réservations

### 2.3 Fonctionnalités côté administrateur

- Gestion des véhicules (ajout, modification, suppression)
- Gestion des réservations
- Gestion des utilisateurs
- Suivi de l'activité globale de la plateforme

## 3. Besoins non fonctionnels

- Sécurité des données utilisateurs (authentification sécurisée)
- Respect du RGPD
- Temps de réponse rapide (< 2 secondes pour les pages principales)
- Interface responsive (mobile / desktop)
- Disponibilité de l'application 24/7 (hors maintenance)
- Code maintenable et structuré (Symfony recommandé)

## 4. Exigences techniques

- Langage : PHP
- Framework : Symfony
- Front-end : HTML / CSS / JavaScript
- Base de données : MySQL
- Outils : Docker, Git

**Choix d'architecture retenu :** application **full-stack Symfony** (Symfony + Twig côté vue),
plutôt qu'une API séparée avec front React/Angular. Ce choix a été retenu car le périmètre
fonctionnel (catalogue, réservation, paiement, back-office) ne nécessite pas une UX ultra-réactive
type SPA, et une application monolithique Symfony permet d'aller plus vite sur un projet solo tout
en couvrant l'ensemble du socle technique imposé (Docker, CI/CD, tests, sécurité).

**API externe intégrée :** Stripe Checkout, pour le paiement en ligne des réservations.

## 5. Contraintes du projet

- Projet réalisé dans un cadre de formation CDA
- Respect des délais de jalons mensuels
- Utilisation de bonnes pratiques de développement
- Respect des règles de sécurité applicative
- Conformité RGPD

## 6. Données personnelles (RGPD)

Dans le cadre de l'application, les données suivantes peuvent être collectées :

- Nom et prénom
- Adresse email
- Mot de passe (haché et sécurisé)
- Historique des réservations

### Finalité du traitement

- Gestion des comptes utilisateurs
- Gestion des réservations

### Conservation des données

Les données sont conservées tant que le compte utilisateur est actif.

### Droits des utilisateurs

Conformément au RGPD, les utilisateurs disposent :

- d'un droit d'accès à leurs données,
- d'un droit de rectification,
- d'un droit de suppression de leur compte.

### Sécurité

- mots de passe chiffrés (hachage),
- accès administrateur restreint et sécurisé,
- protection des données sensibles.

## 7. Risques identifiés

| Risque | Impact | Solution |
|---|---|---|
| Difficulté technique Symfony | Retard dans le développement | Formation + documentation + projets tests |
| Manque de temps | Fonctionnalités incomplètes | Priorisation des features (MVP) |
| Bugs en production | Mauvaise expérience utilisateur | Tests réguliers et correction progressive |

## 8. Critères de réussite

Le projet sera considéré comme réussi si :

- le système de réservation est pleinement fonctionnel,
- la gestion des véhicules est opérationnelle,
- l'application est stable et utilisable sans erreur critique,
- les jalons CDA sont respectés,
- l'interface est intuitive et accessible à un utilisateur non technique.
