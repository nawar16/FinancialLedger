<?php
namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;

class FinancialLogSecurityTest extends TestCase
{
    private \PDO $db;

    protected function setUp(): void
    {
        parent::setUp();
        
        $databaseUrl = getenv('DATABASE_URL') ?: 'postgresql://symfony_app:symfony_app@127.0.0.1:5432/app';
        $cleanUrl = explode('?', $databaseUrl);
        $parsedUrl = parse_url($cleanUrl[0]);
        
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            throw new \RuntimeException("Malformed DATABASE_URL configuration provided.");
        }

        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $parsedUrl['host'],
            $parsedUrl['port'] ?? 5432,
            ltrim($parsedUrl['path'], '/')
        );
        
        $this->db = new \PDO($dsn, $parsedUrl['user'] ?? null, $parsedUrl['pass'] ?? null);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function testSelectAndInsertSucceedAsSymfonyApp(): void
    {
        $uniqueId = random_int(100000, 999999);
        $uniqueAccountId = random_int(100000, 999999);

        $stmt = $this->db->query("SELECT id FROM financial_logs LIMIT 1");
        $this->assertNotFalse($stmt);

        $stmt = $this->db->prepare(
            "INSERT INTO financial_logs (id, account_id, amount, event_type, created_at) " .
            "VALUES (:id, :account_id, '99.99', 'test', NOW())"
        );
        $stmt->execute([
            'id' => $uniqueId,
            'account_id' => $uniqueAccountId
        ]);

        $stmt = $this->db->prepare("SELECT amount FROM financial_logs WHERE account_id = :account_id");
        $stmt->execute(['account_id' => $uniqueAccountId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertEquals(99.99, (float)$row['amount']);
    }

    public function testUpdateThrowsPermissionException(): void
    {
        $uniqueId = random_int(100000, 999999);
        $uniqueAccountId = random_int(100000, 999999);

        $stmt = $this->db->prepare(
            "INSERT INTO financial_logs (id, account_id, amount, event_type, created_at) " .
            "VALUES (:id, :account_id, '49.99', 'update-test', NOW())"
        );
        $stmt->execute([
            'id' => $uniqueId,
            'account_id' => $uniqueAccountId
        ]);

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches('/(?i)(permission denied|insufficient privilege|not allowed|access denied)/');

        $stmt = $this->db->prepare("UPDATE financial_logs SET amount = '0.00' WHERE id = :id");
        $stmt->execute(['id' => $uniqueId]);
    }
}
