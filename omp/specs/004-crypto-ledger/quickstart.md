# Quickstart: Crypto-Ledger

## Overview

This feature implements a tamper‑evident, append‑only ledger for financial documents. It consists of two core services:

- **LedgerHasher** – computes SHA‑256 hashes and verifies chain integrity.
- **CanonicalSerializer** – produces deterministic, fixed‑decimal JSON representations.

## Prerequisites

- Symfony 7.x with Doctrine ORM 3.x
- PostgreSQL database with the `financial_logs` table (columns: `id`, `accountId`, `amount`, `eventType`, `createdAt`)
- `symfony_app` role with SELECT/INSERT permissions on the ledger table

## Getting Started

1. **Install dependencies** (if not already done):
   ```bash
   composer install
   ```

2. **Configure the database**:
   - Ensure the `financial_logs` table exists with the correct schema.
   - Create the `symfony_app` role with appropriate permissions.

3. **Run the security integration test** to verify the ledger works as expected:
   ```bash
   vendor/bin/phpunit backend/tests/integration/FinancialLogSecurityTest.php
   ```
   All tests should pass.

4. **Verify the implementation**:
   - The `LedgerHasher` service can be invoked via the `LedgerHasher` class.
   - The `CanonicalSerializer` can be invoked via the `CanonicalSerializer` class.
   - The `FinancialLog` entity can be created with `id`, `accountId`, `amount`, `eventType`, and `createdAt`.

## Usage Example

```php
// Create a new ledger entry
$log = new FinancialLog(
    id: 'a0eebc99-9c0b-1d4a-8c2c-4d8a4e0b3c7d',
    accountId: 'acc_12345',
    amount: 150.75,
    eventType: 'deposit',
    createdAt: new \DateTimeImmutable()
);

// Serialize deterministically
$json = $log->serialize();
// Output: {"accountId":"acc_12345","amount":150.75,"eventType":"deposit","createdAt":"2026-11-07T12:00:00Z"}
```

## Verification

- Run the full test suite: `vendor/bin/phpunit`
- Ensure all tests pass, including `FinancialLogSecurityTest.php`.
- Confirm that the `symfony_app` role can read and write entries.
- Confirm that tampering (modifying an existing entry) breaks the hash chain.

## Next Steps

- Extend the ledger with additional entity types (e.g., `Transaction`, `AuditRecord`).
- Add more sophisticated security controls (e.g., multi‑signature approval).
- Integrate with the existing financial system for real‑world use cases.
