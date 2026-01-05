#!/bin/bash

# Script de déploiement pour Hostinger
# Usage: ./deploy.sh

echo "🚀 Déploiement ADALEN sur Hostinger"
echo "===================================="

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifier que nous sommes en production
if [ "$APP_ENV" != "prod" ]; then
    echo -e "${YELLOW}⚠️  Attention: APP_ENV n'est pas défini sur 'prod'${NC}"
fi

echo ""
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erreur lors de l'installation des dépendances${NC}"
    exit 1
fi

echo ""
echo "🗑️  Nettoyage du cache..."
php bin/console cache:clear --env=prod --no-debug

echo ""
echo "🔥 Réchauffage du cache..."
php bin/console cache:warmup --env=prod --no-debug

echo ""
echo "📊 Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Avertissement: Erreur lors des migrations (peut être normal si déjà exécutées)${NC}"
fi

echo ""
echo "🔐 Vérification des permissions..."
chmod -R 755 var/
chmod -R 755 public/

echo ""
echo -e "${GREEN}✅ Déploiement terminé avec succès!${NC}"
echo ""
echo "📝 Prochaines étapes:"
echo "   1. Vérifiez que .env est configuré correctement"
echo "   2. Vérifiez les permissions des dossiers var/ et public/"
echo "   3. Testez votre site web"
echo "   4. Vérifiez les logs dans var/log/prod.log"


