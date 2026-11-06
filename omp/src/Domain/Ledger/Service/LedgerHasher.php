<?php

namespace App\Domain\Ledger\Service;

class LedgerHasher
{
    public function hash(string $canonicalPayload, ?string $previousHash = null): string
    {
        $input = $this->concatenate($canonicalPayload, $previousHash);
        return $this->sha256($input);
    }

    public function verifyChain(array $entries): bool
    {
        if (empty($entries)) {
            return true;
        }

        $previousHash = null;

        foreach ($entries as $index => $entry) {
            $canonicalPayload = $entry['canonical_payload'] ?? '';
            $expectedHash = $entry['hash'] ?? '';

            $computedHash = $this->hash($canonicalPayload, $previousHash);

            if ($computedHash !== $expectedHash) {
                return false;
            }

            $previousHash = $computedHash;
        }

        return true;
    }

    private function concatenate(string $canonicalPayload, ?string $previousHash): string
    {
        // Genesis record: previousHash is null, so we use empty string
        $previous = $previousHash ?? '';

        // Simple concatenation with delimiter to avoid ambiguity
        // Using a delimiter that cannot appear in hex SHA-256 output
        return $previous . '|' . $canonicalPayload;
    }

    private function sha256(string $input): string
    {
        return hash('sha256', $input);
    }
}