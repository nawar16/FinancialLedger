# Research: Crypto-Ledger Implementation

## Background

The crypto‑ledger feature implements a tamper‑evident, append‑only ledger for financial documents. It builds on existing Symfony and Doctrine infrastructure.

## Existing Implementation

- **LedgerHasher** (`src/Domain/Ledger/Service/LedgerHasher.php`)
  - Computes SHA‑256 hashes for each ledger entry.
  - Verifies chain integrity by sequentially hashing entries.
  - Handles genesis records (previousHash = null).

- **CanonicalSerializer** (`src/Domain/Ledger/Service/CanonicalSerializer.php`)
  - Sorts keys alphabetically for deterministic output.
  - Converts numeric values to fixed‑decimal strings.
  - Converts dates to ISO‑8601 format.
  - Returns compact JSON without extra whitespace.

- **Integration Test** (`tests/Domain/Ledger/Service/LedgerHasherTest.php`)
  - Verifies deterministic hashing.
  - Verifies chain integrity.
  - Verifies tampering detection (UPDATE breaks chain).
  - Verifies genesis record handling.

## Security Considerations

- All ledger entries are cryptographically hashed, ensuring integrity.
- The `symfony_app` role is granted SELECT/INSERT permissions on the `financial_logs` table.
- Unauthorized modifications are blocked by the hash chain verification.
- No direct write access to the ledger table from outside the service layer.

## Performance

- Hash computation is O(n) per entry (n = number of entries in chain).
- Serialization is O(n) with small constant factor.
- The implementation is lightweight and suitable for high‑throughput ledger recording.

## Testing Strategy

- Unit tests for `LedgerHasher` (deterministic hashing, chain verification, genesis).
- Integration tests for `LedgerHasher` (SELECT/INSERT, UPDATE failure).
- Contract tests for `CanonicalSerializer` (deterministic output, fixed‑decimal conversion).
- Security tests (tampering detection).

## References

- Symfony Security Best Practices (append‑only logs, cryptographic chaining)
- PHP 8.1+ type system (strict typing for serialization)
- Doctrine ORM (PostgreSQL) for persistent storage
