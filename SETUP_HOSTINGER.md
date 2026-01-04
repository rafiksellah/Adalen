# 🚀 Configuration pour Hostinger - ADALEN

## 📋 Informations de connexion

- **Domaine** : adalen-dz.com
- **Base de données** : u765242862_tJ3JM
- **Utilisateur MySQL** : u765242862_ZODIK
- **Mot de passe MySQL** : Montessori@2025

## ⚙️ Étape 1 : Générer APP_SECRET

**IMPORTANT** : Vous DEVEZ générer un nouveau `APP_SECRET` pour la sécurité.

### Sur votre machine locale ou sur le serveur :

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Copiez la valeur générée (64 caractères).

## 📝 Étape 2 : Créer le fichier .env

Sur le serveur Hostinger, créez un fichier `.env` à la racine du projet avec ce contenu :

```env
# Environment
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=COLLEZ_ICI_LA_VALEUR_GENERE

# Database Configuration - Hostinger
DATABASE_URL="mysql://u765242862_ZODIK:Montessori@2025@localhost:3306/u765242862_tJ3JM?serverVersion=8.0&charset=utf8mb4"

# Mailer Configuration - Hostinger SMTP
# Récupérez ces infos dans cPanel -> Email Accounts
MAILER_DSN=smtp://votre_email@adalen-dz.com:mot_de_passe_email@smtp.hostinger.com:587

# Contact Email Configuration
app.contact_email_from=noreply@adalen-dz.com
app.contact_email_to=montessoriadalen@gmail.com
```

## 🔧 Étape 3 : Configuration Email

1. Allez dans **cPanel** → **Email Accounts**
2. Créez ou utilisez un compte email (ex: `noreply@adalen-dz.com`)
3. Notez le mot de passe
4. Mettez à jour `MAILER_DSN` dans `.env` :
   ```env
   MAILER_DSN=smtp://noreply@adalen-dz.com:mot_de_passe@smtp.hostinger.com:587
   ```

## 🚀 Étape 4 : Installation sur le serveur

### Via SSH (Recommandé) :

```bash
# Se connecter
ssh user@adalen-dz.com

# Aller dans le répertoire du projet
cd ~/public_html/../  # ou le chemin où vous avez uploadé les fichiers

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Créer les répertoires
mkdir -p var/cache var/log
chmod -R 755 var/

# Vérifier que .env existe et est configuré
cat .env

# Vider et réchauffer le cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Créer la base de données (si nécessaire)
php bin/console doctrine:database:create --if-not-exists --env=prod

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Charger les données de test (optionnel)
php bin/console doctrine:fixtures:load --no-interaction --env=prod
```

### Via cPanel Terminal :

Exécutez les mêmes commandes via le terminal cPanel.

## ✅ Étape 5 : Vérifications

1. **Tester le site** : https://adalen-dz.com
2. **Vérifier la base de données** : Testez une page qui utilise la BDD
3. **Vérifier les assets** : Images, CSS, JS doivent s'afficher
4. **Tester le formulaire de contact**

## 🐛 Dépannage

### Erreur 500

```bash
# Vérifier les permissions
chmod -R 755 var/
chmod 644 .env

# Vérifier les logs
tail -f var/log/prod.log
```

### Base de données

```bash
# Tester la connexion
php bin/console doctrine:database:create --if-not-exists --env=prod

# Vérifier les migrations
php bin/console doctrine:migrations:status --env=prod
```

### Cache

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

## 📝 Checklist

- [ ] APP_SECRET généré et configuré
- [ ] Fichier .env créé avec les bonnes valeurs
- [ ] Base de données configurée
- [ ] Email SMTP configuré
- [ ] Composer install exécuté
- [ ] Permissions var/ définies (755)
- [ ] Migrations exécutées
- [ ] Cache vidé et réchauffé
- [ ] Site testé et fonctionnel

## 🔗 URLs importantes

- **Site web** : https://adalen-dz.com
- **cPanel** : https://adalen-dz.com:2083 (ou l'URL fournie par Hostinger)
- **FTP** : ftp.adalen-dz.com (ou l'URL fournie par Hostinger)

