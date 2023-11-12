<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Updater\GitHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use LogicException;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231112222224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $existingCount = $this->connection->executeQuery('select count(id) as c from packages')->fetchOne();
        if (!$existingCount) {
            $this->addSql('ALTER TABLE packages ADD COLUMN datetime DATETIME NOT NULL');
            return;
        }

        $nixpkgsPath = $_ENV['PATH_TO_NIXPKGS'] ?? null;
        if (!is_string($nixpkgsPath) || !$nixpkgsPath) {
            throw new LogicException('This migration can only run with having the PATH_TO_NIXPKGS env variable if the database is not empty');
        }
        $this->addSql('ALTER TABLE packages ADD COLUMN datetime DATETIME DEFAULT NULL');
        $revisions = $this->connection->executeQuery('select distinct revision from packages')->fetchFirstColumn();

        $git = new GitHelper();

        foreach ($revisions as $revision) {
            $revision = $git->getRevision($nixpkgsPath, $revision);
            $this->addSql('update packages set datetime = ? where revision = ?', [$revision->dateTime->format('c'), $revision->revision]);
        }

        $this->addSql('CREATE TEMPORARY TABLE __temp__packages AS SELECT id, name, version, revision, datetime FROM packages');
        $this->addSql('DROP TABLE packages');
        $this->addSql('CREATE TABLE packages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(180) NOT NULL, version VARCHAR(180) NOT NULL, revision VARCHAR(180) NOT NULL, datetime DATETIME NOT NULL)');
        $this->addSql('INSERT INTO packages (id, name, version, revision, datetime) SELECT id, name, version, revision, datetime FROM __temp__packages');
        $this->addSql('DROP TABLE __temp__packages');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BB5C0A75E237E06BF1CD3C3 ON packages (name, version)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE packages DROP COLUMN datetime');
    }
}
