<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003000000 extends AbstractMigration
{




    public function getDescription(): string
    {
        return 'Create append-only financial_logs table with JSONB and explicit symfony_app role restrictions';
    }

    public function up(Schema $schema): void
    {
        // Build the log layout with a BIGSERIAL primary key and UTC timestamp tracking
        $this->addSql('CREATE TABLE financial_logs (
            id BIGSERIAL PRIMARY KEY, 
            account_id INT NOT NULL, 
            amount NUMERIC(15, 4) NOT NULL, 
            event_type VARCHAR(64) NOT NULL, 
            payload JSONB DEFAULT \'{}\'::jsonb NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT (CURRENT_TIMESTAMP AT TIME ZONE \'UTC\') NOT NULL
        )');

        // Enforce the database-level security policy: strip mutation rights
        $this->addSql('GRANT SELECT, INSERT ON financial_logs TO symfony_app');
        $this->addSql('REVOKE UPDATE, DELETE ON financial_logs FROM symfony_app');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE financial_logs');
    }
}
