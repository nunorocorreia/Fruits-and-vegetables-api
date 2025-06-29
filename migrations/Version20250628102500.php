<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Temporary migration to seed database with request.json data
 */
final class Version20250628102500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed database with fruits and vegetables data from request.json';
    }

    public function up(Schema $schema): void
    {
        // Insert fruits data
        $this->addSql(<<<'SQL'
            INSERT INTO fruit (name, type, quantity, unit) VALUES 
            ('Apples', 'fruit', 20, 'kg'),
            ('Pears', 'fruit', 3500, 'g'),
            ('Melons', 'fruit', 120, 'kg'),
            ('Berries', 'fruit', 10000, 'g'),
            ('Bananas', 'fruit', 100, 'kg'),
            ('Oranges', 'fruit', 24, 'kg'),
            ('Avocado', 'fruit', 10, 'kg'),
            ('Lettuce', 'fruit', 20830, 'g'),
            ('Kiwi', 'fruit', 10, 'kg'),
            ('Kumquat', 'fruit', 90000, 'g')
        SQL);

        // Insert vegetables data
        $this->addSql(<<<'SQL'
            INSERT INTO vegetable (name, type, quantity, unit) VALUES 
            ('Carrot', 'vegetable', 10922, 'g'),
            ('Beans', 'vegetable', 65000, 'g'),
            ('Beetroot', 'vegetable', 950, 'g'),
            ('Broccoli', 'vegetable', 3, 'kg'),
            ('Tomatoes', 'vegetable', 5, 'kg'),
            ('Celery', 'vegetable', 20, 'kg'),
            ('Cabbage', 'vegetable', 500, 'kg'),
            ('Onion', 'vegetable', 50, 'kg'),
            ('Cucumber', 'vegetable', 8, 'kg'),
            ('Pepper', 'vegetable', 150, 'kg')
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Remove all seeded data
        $this->addSql('DELETE FROM fruit WHERE name IN ("Apples", "Pears", "Melons", "Berries", "Bananas", "Oranges", "Avocado", "Lettuce", "Kiwi", "Kumquat")');
        $this->addSql('DELETE FROM vegetable WHERE name IN ("Carrot", "Beans", "Beetroot", "Broccoli", "Tomatoes", "Celery", "Cabbage", "Onion", "Cucumber", "Pepper")');
    }
} 