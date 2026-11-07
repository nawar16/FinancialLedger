<?php
namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

class LedgerSecurityTest extends TestCase
{
    /**
     * @var \PDO
     */
    private \PDO $db;

    protected function setUp(): void
    {
        parent::setUp();

        $databaseUrl = getenv('DATABASE_URL') ?: 'postgresql://symfony_app:symfony_app@127.0.0.1:5432/app';
        
        $cleanUrl = explode('?', $databaseUrl)[0];
        $parsedUrl = parse_url($cleanUrl);
        
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            throw new \RuntimeException("Malformed DATABASE_URL configuration provided.");
        }

        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $parsedUrl['host'],
            $parsedUrl['port'] ?? 5432,
            ltrim($parsedUrl['path'], '/')
        );

        $user = $parsedUrl['user'] ?? null;
        $password = $parsedUrl['pass'] ?? null;

        $this->db = new \PDO($dsn, $user, $password);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * SC-003 / FR-008: symfony_app role can SELECT and INSERT.
     */
    public function testSelectAndInsertSucceedAsSymfonyApp(): void
    {
        // First clean any previous test row
        $this->db->exec("DELETE FROM financial_logs WHERE account_id = 99999");

        // SELECT should succeed
        $stmt = $this->db->query("SELECT id FROM financial_logs LIMIT 1");
        $this->assertNotFalse($stmt, "SELECT query should succeed");

        // INSERT should succeed (FR-008 verification)
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
     */
    public function testUpdateThrowsPermissionException(): void
    {
        $this->db->exec(
            "INSERT INTO financial_logs (id, account_id, amount, event_type, created_at) " .
            "VALUES (99998, 99998, '49.99', 'update-test', NOW()) ON CONFLICT (id) DO NOTHING"
        );

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches('/(?i)(permission denied|insufficient privilege|not allowed|access denied)/');

        $this->db->exec("UPDATE financial_logs SET amount = '0.00' WHERE id = 99998");
    }
}
