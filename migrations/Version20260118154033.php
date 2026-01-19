<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118154033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert actuality to multi-language support with separate fields for each language';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les nouveaux champs de traduction
        $this->addSql('ALTER TABLE actuality ADD title_fr VARCHAR(255) DEFAULT NULL, ADD title_en VARCHAR(255) DEFAULT NULL, ADD title_ar VARCHAR(255) DEFAULT NULL, ADD description_fr LONGTEXT DEFAULT NULL, ADD description_en LONGTEXT DEFAULT NULL, ADD description_ar LONGTEXT DEFAULT NULL');
        
        // Migrer les données existantes vers title_fr et description_fr
        $this->addSql('UPDATE actuality SET title_fr = title, description_fr = description WHERE title IS NOT NULL');
        
        // Supprimer les anciens champs
        $this->addSql('ALTER TABLE actuality DROP title, DROP description, DROP locale');
    }

    public function down(Schema $schema): void
    {
        // Restaurer les anciens champs
        $this->addSql('ALTER TABLE actuality ADD title VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD locale VARCHAR(5) DEFAULT \'fr\' NOT NULL');
        
        // Migrer les données de title_fr vers title
        $this->addSql('UPDATE actuality SET title = COALESCE(title_fr, title_en, title_ar), description = COALESCE(description_fr, description_en, description_ar), locale = \'fr\'');
        
        // Supprimer les champs de traduction
        $this->addSql('ALTER TABLE actuality DROP title_fr, DROP title_en, DROP title_ar, DROP description_fr, DROP description_en, DROP description_ar');
    }
}
