<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118110542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create activity and animator tables for admin CRUD';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la table activity existe déjà
        $activityTableExists = $this->connection->executeQuery("SHOW TABLES LIKE 'activity'")->rowCount() > 0;
        
        if (!$activityTableExists) {
            $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, age_range VARCHAR(20) DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, number_of_classes INT DEFAULT NULL, duration VARCHAR(50) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
        
        // Vérifier si la table animator existe déjà
        $animatorTableExists = $this->connection->executeQuery("SHOW TABLES LIKE 'animator'")->rowCount() > 0;
        
        if (!$animatorTableExists) {
            $this->addSql('CREATE TABLE animator (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE animator');
    }
}
