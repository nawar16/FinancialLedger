<?php

namespace App\Tests\Integration;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for FinancialLog security.
 * 
 * This test verifies that the symfony_app database role has appropriate restrictions
 * by attempting an UPDATE operation on financial_logs and asserting that a 
 * permission denied exception is thrown.
 */
class LedgerSecurityTest extends TestCase
{
    /**
     * @var \PDO
     */
    private \PDO $db;

    protected function setUp(): void
    {
        parent::setUp();

        // Connect using symfony_app role credentials.
        // In Symfony PHPUnit integration tests, DATABASE_URL is typically configured
        // (e.g., via .env.test) to use the least-privilege application role.
        $dsn = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? 'pgsql://symfony_app:symfony_app@db:5432/app';
        $this->db = new \PDO($dsn);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * SC-003 / FR-008: symfony_app role can SELECT and INSERT.
     * 
     * @return void
     */
    public function testSelectAndInsertSucceedAsSymfonyApp(): void
    {
        // First clean any previous test row
        $this->db->exec("DELETE FROM financial_logs WHERE account_id = 99999");

        // SELECT should succeed
        $stmt = $this->db->query("SELECT id FROM financial_logs LIMIT 1");
        $this->assertNotFalse($stmt, "SELECT query should succeed");

        // INSERT should succeed (FR-008 verification — role has required privileges)
        $this->db->exec(
            "INSERT INTO financial_logs (id, account_id, amount, event_type, created_at) " .
            "VALUES (99999, 99999, '99.99', 'test', NOW())"
        );

        // Verify the inserted row is readable
        $stmt = $this->db->query("SELECT amount FROM financial_logs WHERE account_id = 99999");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($row, "Inserted row should be selectable");
        $this->assertEquals('99.99', $row['amount']);
    }

    /**
     * SC-04 / FR-006 / FR-007: UPDATE must throw a database exception.
     * 
     * The symfony_app role has no UPDATE privilege on financial_logs.
     * This test intentionally attempts an UPDATE and asserts the exception.
     * 
     * @return void
     */
    public function testUpdateThrowsPermissionException(): void
    {
        // Ensure a test row exists (cleanup from previous test if needed)
        $this->db->exec(
            "INSERT INTO financial_logs (id, account_id, amount, event_type, created_at) " .
            "VALUES (99998, 99998, '49.99', 'update-test', NOW()) ON CONFLICT (id) DO NOTHING"
        );

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches('/(?i)(permission denied|insufficient privilege|not allowed|access denied)/');

        // Intentionally attempt UPDATE — should be blocked at DB level (FR-006 / FR-007)
        $this->db->exec("UPDATE financial_logs SET amount = '0.00' WHERE id = 99998");
    }
}