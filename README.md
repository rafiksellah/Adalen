# ADALEN - Centre d'Activités Extra-Scolaires

Projet Symfony pour le centre d'activités extra-scolaires Adalen.

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Symfony CLI (optionnel)

### Étapes d'installation

1. **Installer les dépendances**
```bash
composer install
```

2. **Configurer la base de données**
   
   Créer un fichier `.env.local` à la racine du projet :
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/adalen?serverVersion=8.0&charset=utf8mb4"
```

3. **Créer la base de données**
```bash
php bin/console doctrine:database:create
```

4. **Exécuter les migrations**
```bash
php bin/console doctrine:migrations:migrate
```

5. **Charger les données de test (optionnel)**
```bash
php bin/console doctrine:fixtures:load
```

6. **Lancer le serveur**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

## 📁 Structure du projet

```
src/
├── Controller/
│   ├── HomeController.php
│   ├── ActivityController.php
│   ├── AnimatorController.php
│   └── ContactController.php
├── Entity/
│   ├── Activity.php
│   ├── Animator.php
│   ├── Registration.php
│   └── ContactMessage.php
├── Form/
│   ├── RegistrationType.php
│   └── ContactType.php
└── Repository/
    ├── ActivityRepository.php
    ├── AnimatorRepository.php
    ├── RegistrationRepository.php
    └── ContactMessageRepository.php

templates/
├── base.html.twig
├── home/
│   └── index.html.twig
├── activity/
│   ├── index.html.twig
│   └── register.html.twig
├── animator/
│   └── index.html.twig
├── contact/
│   └── index.html.twig
└── emails/
    └── contact.html.twig

public/
└── assets/
    ├── css/
    │   └── adalen.css
    ├── js/
    │   └── adalen.js
    └── img/
        └── (vos images)
```

## 🎨 Charte Graphique

- **Couleur principale** : #F58220 (Orange Adalen)
- **Couleur secondaire** : #7CB342 (Vert doux)
- **Typographie** : Poppins (titres) & Nunito (texte)
- **Style** : Moderne, chaleureux, adapté aux enfants

## 📄 Pages disponibles

- `/` - Page d'accueil
- `/activities` - Liste des activités
- `/activity/{id}/register` - Formulaire d'inscription
- `/animators` - Liste des animateurs
- `/contact` - Page de contact

## ⚙️ Configuration Email

Dans `config/services.yaml`, configurez :
```yaml
parameters:
    app.contact_email_from: 'noreply@adalen.com'
    app.contact_email_to: 'montessoriadalen@gmail.com'
```

Et dans `.env.local` :
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

## 🗄️ Base de données

Les entités créées :
- **Activity** : Activités extra-scolaires
- **Animator** : Animateurs
- **Registration** : Inscriptions aux activités
- **ContactMessage** : Messages de contact

## 📝 Notes

- Le projet utilise Bootstrap 5
- Design responsive (mobile-first)
- Protection anti-spam (honeypot) sur le formulaire de contact
- Validation des formulaires avec Symfony Validator


