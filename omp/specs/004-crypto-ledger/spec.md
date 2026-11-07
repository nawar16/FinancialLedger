# Feature Specification: Crypto-Ledger

**Feature Branch**: `004-crypto-ledger`  
**Created**: 2026-11-07  
**Status**: Draft  
**Input**: User description: `crypto-ledger`

## User Scenarios & Testing

### User Story 1 – Cryptographic Hash Chain Verification (P1)

**Description**: The ledger must compute SHA-256 hashes for each ledger entry and verify that the hash chain remains intact across entries.

**Why this priority**: Ensures data integrity and prevents tampering of historical records.

**Independent Test**: Can be fully tested by running the integration test suite (`backend/tests/integration/FinancialLogSecurityTest.php`) which verifies that a valid chain passes SELECT/INSERT and that an UPDATE attempt throws a database exception.

**Acceptance Scenarios**:
1. Given a genesis record with no previous hash, when a new entry is added, the hash is computed as `SHA-256(canonical_payload || '|' || previous_hash)` and stored.
2. Given a sequence of entries, when the chain is verified, all hashes match the expected values.
3. Given a tampered entry, when the chain is re-verified, the verification fails.

### User Story 2 – Deterministic Canonical Serialization (P1)

**Description**: Ledger entries must be serialized deterministically so that identical payloads always produce the same JSON representation.

**Why this priority**: Enables reliable storage, comparison, and audit trails.

**Independent Test**: Can be tested by instantiating `FinancialLog` objects with the same payload and verifying that `get_class_methods()` contains zero `set*` methods and that `createdAt` defaults to `\DateTimeImmutable()` UTC.

**Acceptance Scenarios**:
1. Given a payload, when serialized, the output JSON is identical across multiple instantiations.
2. Given a payload, when serialized, the `createdAt` field is set to the current UTC time.
3. Given a payload, when serialized, all numeric values are converted to fixed-decimal strings (no floating-point representation).

### User Story 3 – Integration Security Tests (P1)

**Description**: Integration tests must confirm that the ledger can be accessed by the `symfony_app` role and that unauthorized modifications are blocked.

**Why this priority**: Security is a core requirement for a tamper‑evident ledger.

**Independent Test**: Can be tested by running `backend/tests/integration/FinancialLogSecurityTest.php` which connects as `symfony_app`, performs SELECT/INSERT, and asserts that an UPDATE attempt throws a database exception.

**Acceptance Scenarios**:
1. Given a `symfony_app` role, when it executes `SELECT` on the `financial_logs` table, the query succeeds.
2. Given a `symfony_app` role, when it executes `INSERT` on a new ledger entry, the entry is persisted.
3. Given a `symfony_app` role, when it attempts to `UPDATE` an existing entry, the operation fails with a database exception (permission/privilege error).

## Requirements

- **FR-001**: System MUST compute SHA-256 hashes for each ledger entry using the `LedgerHasher` service.
- **FR-002**: System MUST verify the hash chain integrity using the `LedgerHasher` service.
- **FR-003**: System MUST serialize ledger entries deterministically via `CanonicalSerializer`.
- **FR-004**: System MUST allow `symfony_app` role to read and write ledger entries.
- **FR-005**: System MUST reject unauthorized modifications (tampering detection).

## Key Entities

- **FinancialLog**: Represents a single ledger entry with fields `id`, `accountId`, `amount`, `eventType`, `createdAt`.

## Success Criteria

- **SC-001**: Zero setter methods exist in `FinancialLog` (immutability).
- **SC-002**: `createdAt` defaults to `\DateTimeImmutable()` UTC and is always present.
- **SC-003**: SELECT/INSERT operations succeed for `symfony_app`.
- **SC-004**: UPDATE operations on existing entries throw a database exception.
- **SC-005**: All user stories are independently testable and pass.

## Edge Cases

- What happens when `createdAt` is explicitly set to a non‑UTC value? (Should be ignored / defaulted to UTC)
- What happens when a payload contains non‑numeric values in `amount`? (Should be handled by canonical serializer)
- What happens when the `previousHash` is `null` (genesis record)? (Should be treated as empty string in concatenation)

## Constraints

- Must use Symfony 7.x and Doctrine ORM.
- All tests must pass in CI.
- No external dependencies beyond the existing codebase.

## Assumptions

- The `financial_logs` table already exists with columns `id`, `accountId`, `amount`, `eventType`, `createdAt`.
- The `symfony_app` role has SELECT/INSERT permissions on the table.
- The existing `LedgerHasher` and `CanonicalSerializer` classes are available for use.
