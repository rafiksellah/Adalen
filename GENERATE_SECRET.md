# Générer APP_SECRET pour la production

## ⚠️ IMPORTANT : Générer un APP_SECRET unique

Avant de déployer, vous DEVEZ générer un nouveau `APP_SECRET` pour la sécurité.

## Méthode 1 : Via Symfony Console (Recommandé)

```bash
php bin/console secrets:generate-secret
```

Copiez la valeur générée et remplacez `APP_SECRET` dans `.env.hostinger`

## Méthode 2 : Via PHP

```bash
php -r "echo bin2hex(random_bytes(32));"
```

## Méthode 3 : En ligne

Utilisez un générateur de clé sécurisé en ligne.

## Après génération

1. Copiez la valeur générée
2. Remplacez dans `.env.hostinger` :
   ```env
   APP_SECRET=votre_valeur_generee_ici
   ```
3. Renommez `.env.hostinger` en `.env` sur le serveur


