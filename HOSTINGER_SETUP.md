# 🚀 Guide de Déploiement ADALEN sur Hostinger

## 📋 Checklist Pré-Déploiement

### Sur votre machine locale :

- [ ] Tester le projet en local
- [ ] Vérifier que toutes les fonctionnalités marchent
- [ ] Optimiser les images si nécessaire
- [ ] Vérifier les chemins des assets

## 🔧 Étape 1 : Préparer le Projet

### 1.1 Optimiser pour la production

```bash
# Installer les dépendances sans dev
composer install --no-dev --optimize-autoloader

# Vider le cache
php bin/console cache:clear --env=prod
```

### 1.2 Créer le fichier .env pour la production

Créez un fichier `.env.production` avec ces valeurs (à adapter selon Hostinger) :

```env
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=votre_secret_long_et_aleatoire_ici

DATABASE_URL="mysql://user:password@localhost:3306/nom_base?serverVersion=8.0&charset=utf8mb4"

MAILER_DSN=smtp://username:password@smtp.hostinger.com:587

# Paramètres de contact (optionnel)
app.contact_email_from=noreply@votre-domaine.com
app.contact_email_to=montessoriadalen@gmail.com
```

**⚠️ Important :** Générez un nouveau `APP_SECRET` :
```bash
php bin/console secrets:generate-secret
```

## 📤 Étape 2 : Upload sur Hostinger

### 2.1 Structure sur Hostinger

Sur Hostinger, vous avez généralement cette structure :
```
/home/u123456789/
├── public_html/          (point d'entrée web)
│   ├── index.php
│   ├── assets/
│   └── .htaccess
├── config/
├── src/
├── templates/
├── vendor/
├── var/
├── .env
└── composer.json
```

### 2.2 Méthode 1 : Via FTP (FileZilla, WinSCP, etc.)

**Fichiers à uploader :**

1. **Tout le contenu de `/public/`** → `/public_html/`
2. **Tous les autres dossiers** à la racine :
   - `config/`
   - `src/`
   - `templates/`
   - `migrations/`
   - `vendor/` (ou installer via Composer sur le serveur)
   - `composer.json`
   - `composer.lock`
   - `.env.production` (renommé en `.env`)

**Fichiers à NE PAS uploader :**
- `var/cache/` (sera créé automatiquement)
- `var/log/` (sera créé automatiquement)
- `node_modules/`
- `.git/`
- `.env.local`
- `tests/`

### 2.3 Méthode 2 : Via cPanel File Manager

1. Connectez-vous à cPanel
2. Allez dans "File Manager"
3. Uploadez les fichiers (peut être long pour `vendor/`)

### 2.4 Méthode 3 : Via SSH (Recommandé)

```bash
# Se connecter
ssh user@votre-domaine.com

# Créer un dossier pour le projet
cd ~
mkdir adalen
cd adalen

# Uploader les fichiers via SCP ou rsync depuis votre machine
# Depuis votre machine locale :
scp -r * user@votre-domaine.com:~/adalen/
```

## 🗄️ Étape 3 : Configuration Base de Données

### 3.1 Créer la base de données

1. **cPanel** → **Bases de données MySQL**
2. Créez une nouvelle base de données (ex: `u123456789_adalen`)
3. Créez un utilisateur MySQL
4. Attribuez tous les privilèges à l'utilisateur
5. **Notez** : nom de la base, utilisateur, mot de passe

### 3.2 Mettre à jour .env

Sur le serveur, éditez `.env` :

```env
DATABASE_URL="mysql://u123456789_user:password@localhost:3306/u123456789_adalen?serverVersion=8.0&charset=utf8mb4"
```

## ⚙️ Étape 4 : Configuration Serveur

### 4.1 Vérifier PHP Version

Dans cPanel → **Select PHP Version**, choisissez **PHP 8.2** ou supérieur.

### 4.2 Permissions

Via SSH ou cPanel File Manager :

```bash
chmod 755 var/
chmod 755 var/cache/
chmod 755 var/log/
chmod 644 .env
chmod 755 public/
```

### 4.3 Installer Composer (si nécessaire)

Si Composer n'est pas installé sur Hostinger :

```bash
# Via SSH
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

## 🚀 Étape 5 : Installation Finale

### 5.1 Via SSH (Recommandé)

```bash
# Se connecter
ssh user@votre-domaine.com

# Aller dans le répertoire du projet
cd ~/adalen  # ou le chemin où vous avez uploadé les fichiers

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Créer les répertoires
mkdir -p var/cache var/log
chmod -R 755 var/

# Vider et réchauffer le cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Charger les données de test (optionnel)
php bin/console doctrine:fixtures:load --no-interaction --env=prod
```

### 5.2 Via cPanel Terminal

Si SSH n'est pas disponible, utilisez cPanel Terminal et exécutez les mêmes commandes.

## 📧 Étape 6 : Configuration Email

Dans `.env`, configurez le SMTP Hostinger :

```env
MAILER_DSN=smtp://username:password@smtp.hostinger.com:587
```

**Note :** Récupérez les identifiants SMTP dans cPanel → **Email Accounts**.

## 🔐 Étape 7 : Sécurité

### 7.1 Générer APP_SECRET

```bash
php bin/console secrets:generate-secret
```

Copiez la valeur générée dans `.env` :

```env
APP_SECRET=la_valeur_generee_ici
```

### 7.2 Vérifier .env

Assurez-vous que :
- `APP_ENV=prod`
- `APP_DEBUG=false`
- `APP_SECRET` est défini et unique

## ✅ Étape 8 : Vérifications

### 8.1 Tester le site

1. Accédez à `https://votre-domaine.com`
2. Vérifiez que la page d'accueil s'affiche
3. Testez les différentes pages
4. Vérifiez que les images s'affichent
5. Testez le formulaire de contact

### 8.2 Vérifier les logs

```bash
tail -f var/log/prod.log
```

### 8.3 Vérifier les erreurs

Si vous voyez une erreur 500 :
1. Vérifiez les permissions : `chmod -R 755 var/`
2. Vérifiez `.env`
3. Vérifiez les logs : `var/log/prod.log`
4. Vérifiez que la base de données est accessible

## 🐛 Dépannage

### Erreur 500

**Solutions :**
1. Vérifiez les permissions : `chmod -R 755 var/`
2. Vérifiez `.env` et `APP_SECRET`
3. Vérifiez les logs : `var/log/prod.log`
4. Vérifiez la version PHP (doit être 8.2+)

### Assets ne s'affichent pas

**Solutions :**
1. Vérifiez que `public/assets/` est accessible
2. Vérifiez les chemins dans les templates (utilisez `asset()`)
3. Vérifiez les permissions : `chmod -R 755 public/`

### Base de données

**Solutions :**
1. Vérifiez `DATABASE_URL` dans `.env`
2. Vérifiez que l'utilisateur a les droits
3. Testez la connexion :
   ```bash
   php bin/console doctrine:database:create --if-not-exists
   ```
4. Exécutez les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

### Cache

**Solutions :**
```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

## 📝 Checklist Finale

- [ ] Fichiers uploadés
- [ ] Base de données créée et configurée
- [ ] `.env` configuré avec les bonnes valeurs
- [ ] `APP_SECRET` généré
- [ ] Permissions définies (755 pour var/)
- [ ] Composer install exécuté
- [ ] Migrations exécutées
- [ ] Cache vidé et réchauffé
- [ ] Assets accessibles
- [ ] Email configuré
- [ ] Site testé et fonctionnel
- [ ] Logs vérifiés

## 🔗 Ressources

- [Documentation Symfony Deployment](https://symfony.com/doc/current/deployment.html)
- [Hostinger Help Center](https://www.hostinger.com/tutorials)
- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html)

## 💡 Astuces

1. **Utilisez SSH** si possible (plus rapide et plus fiable)
2. **Sauvegardez** votre base de données régulièrement
3. **Surveillez les logs** : `var/log/prod.log`
4. **Testez** avant de mettre en production
5. **Utilisez HTTPS** (généralement activé par défaut sur Hostinger)

---

**Besoin d'aide ?** Vérifiez les logs et la documentation Symfony.

