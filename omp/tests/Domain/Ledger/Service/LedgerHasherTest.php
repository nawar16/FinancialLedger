<?php

namespace Tests\Domain\Ledger\Service;

require_once __DIR__ . '/../../../../src/Domain/Ledger/Service/CanonicalSerializer.php';
require_once __DIR__ . '/../../../../src/Domain/Ledger/Service/LedgerHasher.php';

use App\Domain\Ledger\Service\CanonicalSerializer;
use App\Domain\Ledger\Service\LedgerHasher;

class LedgerHasherTest
{
    private CanonicalSerializer $serializer;
    private LedgerHasher $hasher;

    public function __construct()
    {
        $this->serializer = new CanonicalSerializer();
        $this->hasher = new LedgerHasher();
    }

    public function runAllTests(): array
    {
        $results = [];

        $results['testDeterministicHash'] = $this->testDeterministicHash();
        $results['testHashChaining'] = $this->testHashChaining();
        $results['testTamperingBreaksChain'] = $this->testTamperingBreaksChain();
        $results['testGenesisRecord'] = $this->testGenesisRecord();

        return $results;
    }

    private function testDeterministicHash(): array
    {
        $payload = [
            'account_id' => '12345',
            'amount' => '100.50',
            'event_type' => 'transfer',
            'timestamp' => '15/06/2026 14:30:00'
        ];

        $canonical = $this->serializer->serialize($payload);
        $hash1 = $this->hasher->hash($canonical);
        $hash2 = $this->hasher->hash($canonical);

        $sameHash = $hash1 === $hash2;

        return [
            'test' => 'Deterministic Hash Test',
            'input' => $payload,
            'canonical_payload' => $canonical,
            'hash1' => $hash1,
            'hash2' => $hash2,
            'passed' => $sameHash,
            'message' => $sameHash ? 'Hash is deterministic - PASS' : 'Hash is not deterministic - FAIL'
        ];
    }

    private function testHashChaining(): array
    {
        $payloads = [
            ['account_id' => '11111', 'amount' => '100', 'event_type' => 'deposit'],
            ['account_id' => '22222', 'amount' => '50.25', 'event_type' => 'withdrawal'],
            ['account_id' => '33333', 'amount' => '75.99', 'event_type' => 'transfer']
        ];

        // Create entries with proper hash chaining
        $entries = [];
        $previousHash = null;

        foreach ($payloads as $payload) {
            $canonical = $this->serializer->serialize($payload);
            $hash = $this->hasher->hash($canonical, $previousHash);

            $entries[] = [
                'canonical_payload' => $canonical,
                'hash' => $hash, // Store the expected hash
                'payload' => $payload
            ];

            $previousHash = $hash;
        }

        $chainWorks = $this->hasher->verifyChain($entries);

        return [
            'test' => 'Hash Chaining Test',
            'entries' => $entries,
            'passed' => $chainWorks,
            'message' => $chainWorks ? 'Hash chain is valid - PASS' : 'Hash chain is broken - FAIL'
        ];
    }

    private function testTamperingBreaksChain(): array
    {
        // Create a valid chain
        $payloads = [
            ['account_id' => '11111', 'amount' => '100', 'event_type' => 'deposit'],
            ['account_id' => '22222', 'amount' => '50.25', 'event_type' => 'withdrawal']
        ];

        // Create entries with proper hash chaining - these are the ORIGINAL valid entries
        $entries = [];
        $previousHash = null;

        foreach ($payloads as $payload) {
            $canonical = $this->serializer->serialize($payload);
            $hash = $this->hasher->hash($canonical, $previousHash);

            $entries[] = [
                'canonical_payload' => $canonical,
                'hash' => $hash, // Store the ORIGINAL expected hash
                'payload' => $payload
            ];

            $previousHash = $hash;
        }

        // NOW tamper: modify the payload but DON'T update the stored hash
        // The stored hash remains the original hash
        $entries[1]['payload']['amount'] = '999.99'; // Tampered value
        // Recalculate canonical payload with tampered data
        $entries[1]['canonical_payload'] = $this->serializer->serialize($entries[1]['payload']);
        // Note: we DO NOT update $entries[1]['hash'] - it stays the original hash

        // Now verify the chain - it should fail because:
        // - For entry 0: hash will match (not tampered)
        // - For entry 1: verifyChain will compute new hash from tampered canonical payload + previous hash
        //   This new computed hash will NOT equal the stored original hash
        $chainWorks = $this->hasher->verifyChain($entries);

        return [
            'test' => 'Tampering Breaks Chain Test',
            'entries' => $entries,
            'passed' => !$chainWorks, // Should be false if tampering breaks chain
            'message' => !$chainWorks ? 'Tampering correctly breaks chain - PASS' : 'Tampering did not break chain - FAIL'
        ];
    }

    private function testGenesisRecord(): array
    {
        $payload = [
            'account_id' => 'GENESIS',
            'amount' => '0',
            'event_type' => 'genesis',
            'timestamp' => '01/01/2026 00:00:00'
        ];

        $canonical = $this->serializer->serialize($payload);
        $hash = $this->hasher->hash($canonical, null); // No previous hash for genesis

        return [
            'test' => 'Genesis Record Test',
            'payload' => $payload,
            'canonical_payload' => $canonical,
            'hash' => $hash,
            'passed' => !empty($hash) && strlen($hash) === 64,
            'message' => (!empty($hash) && strlen($hash) === 64) ? 'Genesis record created successfully - PASS' : 'Genesis record failed - FAIL'
        ];
    }
}

// Run tests
$test = new LedgerHasherTest();
$results = $test->runAllTests();

$allPassed = true;
foreach ($results as $result) {
    $status = $result['passed'] ? 'PASS' : 'FAIL';
    echo $result['message'] . PHP_EOL;
    echo "  Hash: " . ($result['hash'] ?? 'N/A') . PHP_EOL;
    echo "  Status: " . $status . PHP_EOL;
    echo PHP_EOL;

    if (!$result['passed']) {
        $allPassed = false;
    }
}

echo $allPassed ? "All tests passed!" : "Some tests failed!" . PHP_EOL;
exit($allPassed ? 0 : 1);