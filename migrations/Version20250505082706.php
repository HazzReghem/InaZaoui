<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505082706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD roles JSON NOT NULL DEFAULT '["ROLE_USER"]'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD password VARCHAR(255) NOT NULL DEFAULT ''
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP admin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_8d93d649e7927c74 RENAME TO UNIQ_IDENTIFIER_EMAIL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD admin BOOLEAN NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP roles
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP password
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX uniq_identifier_email RENAME TO uniq_8d93d649e7927c74
        SQL);
    }
}
