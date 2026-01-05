<?php
/**
 * Script de vérification pour le déploiement
 * Usage: php check-deployment.php
 */

echo "🔍 Vérification du déploiement ADALEN\n";
echo "=====================================\n\n";

$errors = [];
$warnings = [];

// 1. Vérifier PHP version
echo "1. Vérification de la version PHP...\n";
$phpVersion = phpversion();
if (version_compare($phpVersion, '8.2.0', '>=')) {
    echo "   ✅ PHP $phpVersion (OK)\n";
} else {
    $errors[] = "PHP version $phpVersion est trop ancienne. Requis: 8.2+";
    echo "   ❌ PHP $phpVersion (NOK - Requis: 8.2+)\n";
}

// 2. Vérifier les extensions
echo "\n2. Vérification des extensions PHP...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'xml', 'ctype', 'iconv', 'json', 'curl'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext\n";
    } else {
        $errors[] = "Extension PHP manquante: $ext";
        echo "   ❌ $ext (manquante)\n";
    }
}

// 3. Vérifier les fichiers essentiels
echo "\n3. Vérification des fichiers essentiels...\n";
$requiredFiles = [
    'composer.json',
    'public/index.php',
    'public/.htaccess',
    'config/services.yaml',
    'src/Kernel.php',
];
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
    } else {
        $errors[] = "Fichier manquant: $file";
        echo "   ❌ $file (manquant)\n";
    }
}

// 4. Vérifier les répertoires
echo "\n4. Vérification des répertoires...\n";
$requiredDirs = [
    'config',
    'src',
    'templates',
    'public',
    'var',
];
foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "   ✅ $dir" . ($writable ? " (writable)" : " (non writable)") . "\n";
        if (!$writable && $dir === 'var') {
            $warnings[] = "Le répertoire var/ n'est pas writable. Exécutez: chmod -R 755 var/";
        }
    } else {
        $errors[] = "Répertoire manquant: $dir";
        echo "   ❌ $dir (manquant)\n";
    }
}

// 5. Vérifier .env
echo "\n5. Vérification de .env...\n";
if (file_exists('.env')) {
    echo "   ✅ .env existe\n";
    $envContent = file_get_contents('.env');
    
    if (strpos($envContent, 'APP_ENV=prod') === false) {
        $warnings[] = "APP_ENV n'est pas défini sur 'prod' dans .env";
    }
    
    if (strpos($envContent, 'APP_SECRET=') !== false) {
        if (strpos($envContent, 'APP_SECRET=change_this') !== false) {
            $errors[] = "APP_SECRET doit être changé dans .env";
            echo "   ❌ APP_SECRET n'a pas été changé\n";
        } else {
            echo "   ✅ APP_SECRET est défini\n";
        }
    } else {
        $errors[] = "APP_SECRET manquant dans .env";
        echo "   ❌ APP_SECRET manquant\n";
    }
    
    if (strpos($envContent, 'DATABASE_URL=') !== false) {
        echo "   ✅ DATABASE_URL est défini\n";
    } else {
        $errors[] = "DATABASE_URL manquant dans .env";
        echo "   ❌ DATABASE_URL manquant\n";
    }
} else {
    $errors[] = "Fichier .env manquant. Copiez .env.example vers .env";
    echo "   ❌ .env manquant\n";
}

// 6. Vérifier vendor/
echo "\n6. Vérification de vendor/...\n";
if (is_dir('vendor') && file_exists('vendor/autoload.php')) {
    echo "   ✅ vendor/ existe\n";
} else {
    $warnings[] = "vendor/ n'existe pas. Exécutez: composer install --no-dev --optimize-autoloader";
    echo "   ⚠️  vendor/ manquant (exécutez: composer install)\n";
}

// 7. Vérifier les permissions var/
echo "\n7. Vérification des permissions...\n";
if (is_dir('var')) {
    if (is_writable('var')) {
        echo "   ✅ var/ est writable\n";
    } else {
        $errors[] = "var/ n'est pas writable. Exécutez: chmod -R 755 var/";
        echo "   ❌ var/ n'est pas writable\n";
    }
    
    if (!is_dir('var/cache')) {
        $warnings[] = "var/cache/ n'existe pas. Il sera créé automatiquement.";
    }
    
    if (!is_dir('var/log')) {
        $warnings[] = "var/log/ n'existe pas. Il sera créé automatiquement.";
    }
} else {
    $errors[] = "var/ n'existe pas";
    echo "   ❌ var/ n'existe pas\n";
}

// Résumé
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RÉSUMÉ\n";
echo str_repeat("=", 50) . "\n\n";

if (empty($errors) && empty($warnings)) {
    echo "✅ Tout est OK ! Votre déploiement est prêt.\n";
    exit(0);
}

if (!empty($errors)) {
    echo "❌ ERREURS CRITIQUES (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   • $warning\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ Le déploiement n'est pas prêt. Corrigez les erreurs ci-dessus.\n";
    exit(1);
} else {
    echo "✅ Le déploiement est prêt, mais vérifiez les avertissements.\n";
    exit(0);
}


