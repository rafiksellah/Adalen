# Guide de Déploiement ADALEN sur Hostinger

## 📋 Prérequis

- Compte Hostinger avec accès FTP/cPanel
- Base de données MySQL créée sur Hostinger
- PHP 8.2+ sur le serveur
- Accès SSH (recommandé) ou FTP

## 🔧 Étape 1 : Préparation du Projet

### 1.1 Optimiser pour la production

```bash
# Installer les dépendances sans dev
composer install --no-dev --optimize-autoloader

# Compiler les assets (si vous avez Webpack Encore)
# npm run build

# Vider le cache
php bin/console cache:clear --env=prod
```

### 1.2 Créer le fichier .env.production

Créez un fichier `.env.production` avec vos variables d'environnement :

```env
APP_ENV=prod
APP_SECRET=votre_secret_ici
DATABASE_URL="mysql://user:password@localhost:3306/nom_base?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN=smtp://user:pass@smtp.hostinger.com:587
```

## 📤 Étape 2 : Upload des Fichiers

### 2.1 Fichiers à uploader

**À UPLOADER :**
- `/public/` → `public_html/` (ou `www/` selon Hostinger)
- `/config/`
- `/src/`
- `/templates/`
- `/vendor/` (ou installer via Composer sur le serveur)
- `/migrations/`
- `composer.json`
- `composer.lock`
- `.env.production` (renommé en `.env` sur le serveur)

**À NE PAS UPLOADER :**
- `/var/` (sera créé automatiquement)
- `/node_modules/`
- `/.git/`
- `/.env.local`
- `/tests/`

### 2.2 Structure recommandée sur Hostinger

```
public_html/
├── index.php (point d'entrée Symfony)
├── assets/
├── .htaccess
├── ...
../ (niveau parent)
├── config/
├── src/
├── templates/
├── vendor/
├── var/
├── .env
└── composer.json
```

## 🗄️ Étape 3 : Configuration de la Base de Données

### 3.1 Créer la base de données sur Hostinger

1. Connectez-vous à cPanel
2. Allez dans "Bases de données MySQL"
3. Créez une nouvelle base de données (ex: `u123456789_adalen`)
4. Créez un utilisateur et attribuez-lui tous les privilèges
5. Notez les identifiants

### 3.2 Mettre à jour .env

```env
DATABASE_URL="mysql://user:password@localhost:3306/u123456789_adalen?serverVersion=8.0&charset=utf8mb4"
```

## ⚙️ Étape 4 : Configuration du Serveur

### 4.1 Fichier .htaccess dans public/

Créez/modifiez `public/.htaccess` :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
    RewriteRule ^(.*) - [E=BASE:%1]
    RewriteCond %{HTTP:Authorization} .
    RewriteRule ^ - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{ENV:REDIRECT_STATUS} =""
    RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ %{ENV:BASE}/index.php [L]
</IfModule>
```

### 4.2 Point d'entrée (public/index.php)

Vérifiez que `public/index.php` existe et pointe vers le bon répertoire.

### 4.3 Permissions

Via SSH ou cPanel File Manager, définissez les permissions :

```bash
chmod 755 var/
chmod 755 var/cache/
chmod 755 var/log/
chmod 644 .env
```

## 🚀 Étape 5 : Installation sur le Serveur

### 5.1 Via SSH (Recommandé)

```bash
# Se connecter au serveur
ssh user@votre-domaine.com

# Aller dans le répertoire du projet
cd public_html/../

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Créer les répertoires nécessaires
mkdir -p var/cache var/log
chmod -R 755 var/

# Vider le cache
php bin/console cache:clear --env=prod

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

### 5.2 Via FTP + cPanel

1. Uploadez tous les fichiers via FTP
2. Installez Composer via cPanel Terminal (si disponible)
3. Exécutez les commandes ci-dessus via Terminal

## 🔐 Étape 6 : Configuration de Sécurité

### 6.1 Variables d'environnement

Assurez-vous que `.env` contient :

```env
APP_ENV=prod
APP_SECRET=changez_cette_valeur_par_une_cle_secrete_longue
DATABASE_URL="mysql://..."
```

Générez un nouveau `APP_SECRET` :

```bash
php bin/console secrets:generate-secret
```

### 6.2 Protéger les fichiers sensibles

Ajoutez dans `.htaccess` à la racine :

```apache
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## 📧 Étape 7 : Configuration Email

Dans `.env`, configurez le SMTP Hostinger :

```env
MAILER_DSN=smtp://username:password@smtp.hostinger.com:587
```

## ✅ Étape 8 : Vérifications Finales

1. **Vérifier les routes** : Accédez à `https://votre-domaine.com`
2. **Vérifier la base de données** : Testez une page qui utilise la BDD
3. **Vérifier les assets** : Images, CSS, JS doivent s'afficher
4. **Vérifier les logs** : `var/log/prod.log` pour les erreurs

## 🐛 Dépannage

### Erreur 500

- Vérifiez les permissions : `chmod -R 755 var/`
- Vérifiez les logs : `var/log/prod.log`
- Vérifiez `.env` et `APP_SECRET`

### Assets ne s'affichent pas

- Vérifiez que `public/assets/` est accessible
- Vérifiez les chemins dans les templates (utilisez `asset()`)

### Base de données

- Vérifiez `DATABASE_URL` dans `.env`
- Vérifiez que l'utilisateur a les droits
- Exécutez les migrations : `php bin/console doctrine:migrations:migrate`

### Cache

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

## 📝 Checklist de Déploiement

- [ ] Fichiers uploadés sur le serveur
- [ ] Base de données créée et configurée
- [ ] `.env` configuré avec les bonnes valeurs
- [ ] `APP_SECRET` généré et sécurisé
- [ ] Permissions définies (755 pour var/)
- [ ] Composer install exécuté
- [ ] Migrations exécutées
- [ ] Cache vidé et réchauffé
- [ ] Assets accessibles
- [ ] Email configuré
- [ ] Site testé et fonctionnel

## 🔗 Ressources

- [Documentation Symfony Deployment](https://symfony.com/doc/current/deployment.html)
- [Hostinger Documentation](https://www.hostinger.com/tutorials)


