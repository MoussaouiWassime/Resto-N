# Resto'N - Application de Gestion de Restaurants
## Présentation du projet

### Introduction

**Resto'N** est une application web Symfony permettant aux restaurateurs de gérer les commandes, réservations, stocks, et d'accéder à diverses fonctionnalités comme les statistiques et un chatbot intelligent.

---

## Fonctionnalités

- **Page d'accueil** : Liste des restaurants triés par catégories avec barre de recherche
- **Gestion de profil** : Inscription, connexion, modification des informations personnelles
- **Gestion des restaurants** : Création, modification et suppression de restaurants
- **Réservations** : Système de réservation avec vérification des horaires et disponibilité des tables
- **Commandes** : Prise de commandes en ligne (sur place ou à emporter)
- **Statistiques** : 
  - Chiffre d'affaires journalier
  - Nombre de visites par jour
  - Nombre de commandes par jour
- **Avis clients** : Système de notation et commentaires
- **Gestion des stocks** : Suivi des produits et stocks par restaurant
- **Chatbot IA** : Assistant intelligent pour guider les utilisateurs (Mistral AI)
- **Gestion des rôles** : Propriétaires, serveurs et système d'invitations par email
- **Interface d'administration** : EasyAdmin pour la gestion globale (accessible aux admins)

---

## Installation avec Docker

### Prérequis

- [Docker](https://www.docker.com/get-started) et Docker Compose installés

### Configuration

1. **Clonez le dépôt** :

```bash
git clone <url-du-repo>
cd sae3-01
```

2. **Configurez les variables d'environnement** dans le fichier `compose.yml` :

```yaml
environment:
    # Configuration email (Gmail) - Mot de passe d'application requis
    - MAILER_DSN=gmail://VOTRE_EMAIL@gmail.com:VOTRE_MOT_DE_PASSE_APPLICATION@default
    
    # Clé API Mistral pour le chatbot (gratuit sur https://console.mistral.ai/home)
    - MISTRAL_API_KEY=votre_cle_api_mistral
```

### Démarrage

1. **Construisez et lancez les conteneurs** :

```bash
docker compose up -d --build
```

2. **Installez les dépendances Composer** (dans le conteneur) :

```bash
docker exec -it symfony_app composer install
```

3. **Initialisez la base de données** :

```bash
docker exec -it symfony_app composer db
```

### Accès à l'application

Naviguez vers : [http://127.0.0.1:8080/](http://127.0.0.1:8080/)

---

## Installation sans Docker (Serveur Web local)

### Prérequis

- PHP >= 8.2 avec extensions : intl, mysql, zip, gd, mbstring, curl, xml, bcmath
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- MySQL 8.0 ou MariaDB

### Installation

1. **Installez les dépendances** :

```bash
composer install
```

2. **Copiez le fichier `.env` en `.env.local`** et configurez :

```env
# Base de données
DATABASE_URL="mysql://login:password@127.0.0.1:3306/nom_base?serverVersion=8.0&charset=utf8mb4"

# Email (Gmail avec mot de passe d'application)
# Voir : https://support.google.com/mail/answer/185833?hl=fr
MAILER_DSN=gmail://votre_email@gmail.com:mot_de_passe_app@default

# Clé API Mistral pour le chatbot
# Obtenir sur : https://console.mistral.ai/home
MISTRAL_API_KEY=votre_cle_api
```

3. **Créez une migration et initialisez la base de données** :

```bash
php bin/console make:migration
composer db
```

### Démarrer le serveur Web

```bash
composer start
```

Accédez à : [https://127.0.0.1:8000/](https://127.0.0.1:8000/)

---

## Déploiement

Le site est déployé ici : https://moussaoui-wassime.hikarima.com/

---

## Identifiants de connexion (Fixtures)

> **Mot de passe pour tous les comptes** : `test`

### Administrateurs

| Email | Rôle supplémentaire |
|-------|---------------------|
| `cutrona@example.com` | Propriétaire "Restaurant Cutrona" + Admin |
| `ho@example.com` | Serveur "Restaurant Cutrona" + Admin |
| `moussaoui@example.com` | Serveur "Restaurant Cutrona" + Admin |

### Structure des rôles

Pour chaque restaurant créé (5 au total) :
- **1 Propriétaire** (OWNER) : Accès complet à la gestion du restaurant
- **3 Serveurs** (SERVER) : Accès aux commandes et réservations

### Utilisateurs supplémentaires

- **10 utilisateurs aléatoires** sont générés automatiquement avec le mot de passe `test`

---

## Scripts Composer disponibles

| Commande | Description |
|----------|-------------|
| `composer start` | Lance le serveur web Symfony (`symfony serve`) |
| `composer db` | Réinitialise complètement la base de données avec les fixtures |
| `composer test:phpcs` | Vérifie le code PHP avec PHP CS Fixer |
| `composer fix:phpcs` | Corrige automatiquement le code PHP |
| `composer test:twigcs` | Vérifie le code Twig avec Twig CS Fixer |
| `composer fix:twigcs` | Corrige automatiquement le code Twig |
| `composer test` | Exécute `test:phpcs` et `test:twigcs` |
| `composer fix` | Exécute `fix:phpcs` et `fix:twigcs` |
| `composer test:codeception` | Lance les tests Codeception |

---

## Style de codage

Le code suit les recommandations [Symfony](https://symfony.com/doc/current/contributing/code/standards.html).

### Configurer PhpStorm

Configurez l'intégration de PHP Coding Standards Fixer en fixant le jeu de règles sur `Custom` et en désignant `.php-cs-fixer.php` comme fichier de configuration.

---

## Architecture technique

### Stack technologique

- **Framework** : Symfony 7.3
- **Base de données** : MySQL 8.0 (Doctrine ORM)
- **Front-end** : Twig, Stimulus, Turbo
- **Administration** : EasyAdmin 4
- **Tests** : Codeception, PHPUnit
- **Conteneurisation** : Docker (Ubuntu 24.04, Apache, PHP 8.2)

### Structure du projet

```
src/
├── Controller/         # Contrôleurs (API, pages, admin)
│   └── Admin/          # Contrôleurs EasyAdmin
├── Entity/             # Entités Doctrine
├── Enum/               # Énumérations (DishCategory, OrderStatus, etc.)
├── Factory/            # Factories Zenstruck Foundry
├── Form/               # Formulaires Symfony
├── Repository/         # Repositories Doctrine
├── Security/           # Voters et authentification
└── Service/            # Services métier (StatisticService, MistralAiService)
```

## Remerciments et utilisations de l'ia

Merci à Damien HO, qui a participé au developpement de ce projet.
La mise en forme de ce projet a été réalisée avec l'aide de l'IA Gemini 3 Pro. Une refonte est en cours.
