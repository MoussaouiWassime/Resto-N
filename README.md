# Resto'N

## Auteurs :

- HO Damien
- MOUSSAOUI Wassime  
- VIGNE Lucas  

## Présentation du projet : 

### Introduction :

Le Resto'N est une application permettant aux restaurateurs de gérer les commandes, réservations depuis ce site, et accéder à divers fonctionnalités comme les statistiques.

## Fonctionnalités :

- La page d'accueil contient la liste de tous les restaurants triés par catégories. Dans la barre de navigation, vous trouverez deux boutons : un pour créer un compte (inscription), et un autre pour se connecter (connexion).
- Une fois connecté, un nouveau bouton apparaîtra dans la barre de navigation, il vous emmènera dans votre profil où vous pouvez faire réaliser plusieurs actions tels que créer un restaurant, se déconnecter, modifier les informations du compte, et créer un restaurants. Vous pouvez également accéder à vos historiques de réservations/commandes. Par ailleurs, si vous travaillez dans un restaurant, il sera affiché sur votre profil avec un lien vers ce restaurant.
- Vous pouvez naviguer dans la liste des restaurants et réserver/commander au restaurant de votre choix. Vous serez emmené dans un formulaire où vous devrez le remplir. Une fois validé, vous serez emmené dans l'historique de vos réservations/commandes.
- En tant que propriétaire/serveur d'un restaurant, vous avez accès à la gestion du restaurant depuis la page des détails de celui-ci : Vous pourrez gérer les réservations/commandes. Par ailleurs, en tant que propriétaire, vous pouvez également accéder aux statistiques du restaurant, modifier ses informations, ajouter un plat, et le supprimer. De plus, vous avez la possibilité d'inviter une personne à devenir serveur dans votre restaurant : un mail sera envoyé à ce dernier,  s'il n'a pas de compte dans le site, il sera invité à en créer un.
- Il y a 3 types de statistiques :
  - Le chiffre d'affaires journalier
  - Le nombre de visites par jour
  - Le nombre de commandes par jour

  Ces données sont mises à jour à chaque fois qu'une réservation/commande est crée/annulée/supprimée ou modifiée dans le cas de la réservation.
- Le site comprend un chatbot qui pourra guider le client vers la page demandée.
- En tant que propriétaire, vous avez accès à la gestion de vos stocks depuis un lien dans le menu "Gestion du Restaurant". Vous pouvez ajouter, supprimer et modifier des produits dans vos stocks.
- L'administrateur du site a accès au back-office depuis le bouton "admin" dans la barre de navigation, où il peut naviguer à travers les différentes entités de la base de données, afin de supprimer/modifier/créer, selon l'entité.

## Configuration :

Afin de tester le site en local, veuillez suivre ces étapes : 
- Installation des dépendances avec`composer install`
- Copier le fichier `.env` en `.env.local` et : 
  - Adapter la variable `DATABASE_URL` selon ce modèle : `DATABASE_URL="mysql://<login>:<password>@mysql:3306/<db_name>?serverVersion=mariadb-10.2.25&charset=utf8mb4"`
  - Adapter la variable du google mailer. Le mot de passe d'application d'un compte google obtensible par cette méthode : https://support.google.com/mail/answer/185833?hl=fr
  - Obtener votre clé api mistral pour le chat bot ici (gratuit dispo) : https://console.mistral.ai/home 
- Créer une migration `php bin/console make:migration`
- Lancer ce script : `composer db` afin de créer la base de données, et générer les fixtures
- Lancer le serveur local (voir Scripts)

### Identifiants : 

Le mot de passe de tous les comptes est "test".

- cutrona@example.com : propriétaire "Restaurant Cutrona" + admin
- ho@example.com : serveur "Restaurant Cutrona" + admin
- moussaoui@example.com : serveur "Restaurant Cutrona" + admin

## Déploiement : 

Le site est déployé ici : http://10.31.33.99/

## Scripts :

- `composer start` : Lancer le serveur local 
- `composer db` : construire la base de données, et charger les données factices 
- `composer fix:phpcs` : Lancer la commande de correctifs de PHP CS Fixer
- `composer test:phpcs` : Lancer la commande de tests de PHP CS Fixer
- `composer fix:twigcs` : Lancer la commande de correctifs de Twig CS Fixer
- `composer test:twigcs` : Lancer la commande de tests de Twig CS Fixer
- `composer fix` Lancer les commandes de correctifs de PHP CS Fixer et de Twig CS Fixer
- `composer test` Lancer les commandes de tests de PHP CS Fixer et de Twig CS Fixer
- `test:codeception` Lancer les commandes de tests de CodeCeption
