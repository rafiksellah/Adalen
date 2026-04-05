<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260405120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table contact_request (messages formulaire contact)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact_request (
            id INT AUTO_INCREMENT NOT NULL,
            nom VARCHAR(150) NOT NULL,
            email VARCHAR(180) NOT NULL,
            sujet VARCHAR(200) NOT NULL,
            message LONGTEXT NOT NULL,
            locale VARCHAR(5) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_request');
    }
}
