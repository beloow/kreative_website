# Kreative Studio — Site vitrine Symfony

Site vitrine pour **Kreative Studio**, agence de marketing digital externalisé pour PME.

Construit avec **Symfony 7**, **Doctrine ORM (MySQL)**, **EasyAdminBundle** pour l'espace
d'administration, et **Symfony Mailer** pour le formulaire de contact.

> ⚠️ Ce projet est livré en code source. Comme l'environnement qui l'a généré n'a pas accès à
> Packagist, **personne n'a encore lancé `composer install` ni de migration** — c'est la première
> chose à faire en local (étapes ci-dessous). Tout le code (entités, contrôleurs, templates, CSS)
> est complet et prêt à l'emploi une fois les dépendances installées.

## 1. Prérequis

- PHP 8.2 ou supérieur, avec les extensions `ctype`, `iconv`, `pdo_mysql`
- Composer
- MySQL 8 (ou MariaDB équivalent)
- Symfony CLI (optionnel, pratique pour le serveur local) : https://symfony.com/download

## 2. Installation

```bash
cd kreative-studio
composer install
```

## 3. Configuration

Copie `.env` en `.env.local` et adapte au minimum :

```bash
cp .env .env.local
```

- `DATABASE_URL` → tes identifiants MySQL locaux
- `MAILER_DSN` → ton SMTP réel (Gmail, Brevo, Mailjet, OVH...) pour que le formulaire de contact
  envoie réellement des e-mails. En dev tu peux laisser `MAILER_DSN=null://null` pour désactiver
  l'envoi sans erreur.
- `CONTACT_RECIPIENT` → l'adresse qui doit recevoir les demandes du formulaire
- `APP_SECRET` → génère une valeur aléatoire unique

## 4. Base de données

```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Puis crée ton compte admin et les services de démonstration :

```bash
php bin/console app:create-admin admin@kreative-studio.fr "MotDePasseSolide123!"
php bin/console app:seed-services
```

## 5. Lancer le site en local

```bash
symfony server:start
# ou
php -S 127.0.0.1:8000 -t public
```

- Site public : http://127.0.0.1:8000
- Espace admin : http://127.0.0.1:8000/admin (connexion avec le compte créé à l'étape 4)

## Structure du site

| Page | Route | Description |
|---|---|---|
| Accueil | `/` | Hero + aperçu des 3 premiers services + CTA |
| À propos | `/a-propos` | Présentation du studio, chiffres clés, méthode |
| Services | `/services` | Liste complète des services (gérée en admin) |
| Contact | `/contact` | Formulaire → email + sauvegarde en base |
| Admin | `/admin` | Gestion des Services + consultation des leads |

## Espace admin

L'admin (EasyAdminBundle) permet de :
- **Services** : créer/modifier/réordonner/masquer les offres affichées sur le site
- **Demandes de contact** : consulter chaque message reçu via le formulaire, marquer comme traité

L'accès est protégé par un compte utilisateur (table `app_user`, mot de passe hashé), pas par un
simple mot de passe partagé.

## À propos de HubSpot

Le formulaire de contact enregistre chaque demande en base **et** envoie un e-mail de
notification. Si tu utilises HubSpot comme CRM principal, deux options pour brancher les deux :

1. **Simple** : garde ce formulaire tel quel, et exporte/relance manuellement les leads dans
   HubSpot, ou connecte une automatisation (Zapier/Make) sur la boîte mail de notification.
2. **Direct** : ajoute un appel à l'API HubSpot (`Forms API` ou `CRM API`) dans
   `src/Controller/ContactController.php`, juste après le `flush()`, pour créer le contact
   directement dans HubSpot en plus du `.send()` du mailer. Je peux te fournir ce bout de code si
   tu me donnes ton Portal ID / clé d'API privée HubSpot.

## Design

- Fond encre profonde `#0A1726`, accent sable `#d2b48c`
- Typographies : **Fraunces** (titres) + **Inter** (texte) + **IBM Plex Mono** (labels/données)
- Élément signature : la "courbe de croissance" (Visibilité → Trafic → Leads → Clients) reprise
  en hero et sur la page À propos
- Toutes les images sont des illustrations SVG générées pour la démo (aucune photo nécessaire
  pour lancer le site) — à remplacer par tes vraies photos/visuels quand tu les auras.

## Prochaines étapes suggérées

- Remplacer les textes provisoires par tes vrais contenus
- Ajouter de vraies photos (équipe, clients, cas d'usage) dans `public/images/`
- Brancher un vrai nom de domaine + certificat SSL en production
- Ajouter Google Analytics / Search Console une fois le site en ligne
