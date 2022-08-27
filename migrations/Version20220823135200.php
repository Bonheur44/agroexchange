<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220823135200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carrier ADD phone INT DEFAULT NULL');
        $this->addSql('ALTER TABLE consumer CHANGE phone phone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE producer CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE balance balance INT DEFAULT 0, CHANGE status status INT DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carrier DROP phone');
        $this->addSql('ALTER TABLE consumer CHANGE phone phone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE producer CHANGE phone phone VARCHAR(255) NOT NULL, CHANGE balance balance INT DEFAULT 0 NOT NULL, CHANGE status status INT DEFAULT 0 NOT NULL');
    }
}
