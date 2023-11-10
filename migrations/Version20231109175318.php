<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231109175318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof SqlitePlatform, 'Migration can only be executed safely on \'sqlite\'.');
        $this->addSql('CREATE TABLE packages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(180) NOT NULL, version VARCHAR(180) NOT NULL, revision VARCHAR(180) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BB5C0A75E237E06BF1CD3C3 ON packages (name, version)');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(!$this->connection->getDatabasePlatform() instanceof SqlitePlatform, 'Migration can only be executed safely on \'sqlite\'.');
        $this->addSql('DROP TABLE packages');
    }
}
