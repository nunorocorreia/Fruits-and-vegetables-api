<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250628102432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            <<<'SQL'
            CREATE TABLE fruit (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL, 
                quantity INTEGER NOT NULL, 
                date_add DATETIME NOT NULL,
                date_upd DATETIME NOT NULL
            )
        SQL
        );
        $this->addSql(
            <<<'SQL'
            CREATE TABLE vegetable (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                quantity INTEGER NOT NULL,
                date_add DATETIME NOT NULL,
                date_upd DATETIME NOT NULL
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            <<<'SQL'
            DROP TABLE fruit
        SQL
        );
        $this->addSql(
            <<<'SQL'
            DROP TABLE vegetable
        SQL
        );
    }
}
