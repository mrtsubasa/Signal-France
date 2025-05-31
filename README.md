# Signal-France

## Présentation

Signal-France est une application web de signalement d’incidents (personnes ou contenus). Elle permet aux utilisateurs de déclarer des incidents et aux administrateurs de traiter, valider et gérer ces signalements via un panel d’administration complet. Le projet vise à simplifier la gestion des alertes, à assurer le suivi des incidents signalés, et à offrir un espace sécurisé pour la communauté.

## Fonctionnalités principales

### Côté utilisateur
- Création et gestion de comptes utilisateurs
- Système de signalement (personne ou contenu)
- Accès à la recherche des signalements (accès complet uniquement pour les utilisateurs connectés ; seuls les signalements validés sont visibles)
- Guide d’utilisation du site
- Foire aux questions (FAQ)
- Accès professionnels dédiés

### Côté administrateur (Panel Admin)
- Gestion des utilisateurs (création, modification, suppression)
- Gestion et validation des signalements/alertes
- Gestion des messages envoyés via la page de contact
- Consultation et gestion des logs du projet

## Installation

### Prérequis
- PHP (>=7.4 recommandé)
- Composer
- Serveur Web (Apache, Nginx, ou serveur PHP intégré)
- Base de données (MySQL, MariaDB, etc.)

### Étapes d’installation

1. Clone le dépôt :
   ```bash
   git clone https://github.com/mrtsubasa/Signal-France.git
   cd Signal-France
   ```
2. Installe les dépendances PHP :
   ```bash
   composer install
   ```
3. Configure le fichier `.env` avec les informations de ta base de données et autres paramètres nécessaires.
4. Exécute les migrations si besoin (adapter selon l’outil utilisé).
5. Lance le serveur :
   ```bash
   php -S localhost:8000 -t public
   ```

## Utilisation

1. Accède à l’application via [http://localhost:8000](http://localhost:8000)
2. Inscris-toi ou connecte-toi pour accéder à toutes les fonctionnalités
3. Utilise le menu pour signaler un incident, accéder à la FAQ ou au guide d’utilisation
4. Les administrateurs peuvent accéder au panel admin pour gérer les utilisateurs, signalements, messages et logs

## Structure du projet

```plaintext
├── public/           # Fichiers accessibles publiquement (point d’entrée, assets, etc.)
├── src/              # Code source de l’application
├── config/           # Fichiers de configuration
├── templates/        # Templates et vues HTML
├── logs/             # Fichiers de logs
├── README.md         # Ce fichier
```

## Technologies utilisées

- PHP (95%)
- SCSS (4%)
- Hack (0.9%)
- [Ajouter d’autres si nécessaire]

## FAQ & Guide

Un guide d’utilisation et une FAQ sont accessibles depuis l’interface pour accompagner les utilisateurs dans la prise en main du site et répondre aux questions courantes.

## Contribution

Les contributions sont les bienvenues. Pour contribuer :
1. Fork le projet
2. Crée une branche pour ta fonctionnalité (`git checkout -b feature/ma-fonctionnalite`)
3. Commit tes modifications (`git commit -am 'Ajout d’une nouvelle fonctionnalité'`)
4. Push la branche (`git push origin feature/ma-fonctionnalite`)
5. Ouvre une Pull Request

## Licence

[MIT]
