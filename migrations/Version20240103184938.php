<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240103184938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__packages AS SELECT id, name, version, revision, datetime FROM packages');
        $this->addSql('DROP TABLE packages');
        $this->addSql('CREATE TABLE packages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(180) NOT NULL, version VARCHAR(180) NOT NULL, revision VARCHAR(180) NOT NULL, datetime DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , unfree BOOLEAN DEFAULT NULL, platforms CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO packages (id, name, version, revision, datetime) SELECT id, name, version, revision, datetime FROM __temp__packages');
        $this->addSql('DROP TABLE __temp__packages');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BB5C0A75E237E06BF1CD3C3 ON packages (name, version)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__packages AS SELECT id, datetime, name, version, revision FROM packages');
        $this->addSql('DROP TABLE packages');
        $this->addSql('CREATE TABLE packages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, datetime DATETIME NOT NULL, name VARCHAR(180) NOT NULL, version VARCHAR(180) NOT NULL, revision VARCHAR(180) NOT NULL)');
        $this->addSql('INSERT INTO packages (id, datetime, name, version, revision) SELECT id, datetime, name, version, revision FROM __temp__packages');
        $this->addSql('DROP TABLE __temp__packages');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BB5C0A75E237E06BF1CD3C3 ON packages (name, version)');
    }
}
