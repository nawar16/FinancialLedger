# Phase 1: Data Model — 003-domain-entity-and-security-tests

## Entities

### FinancialLog
- **Type**: Doctrine ORM Entity (immutable domain entity)
- **Table**: `financial_logs`
- **Fields**:
  - `id`: bigint, PK, auto-increment (or UUID if required by schema from 002 — using bigint to match User pattern)
  - `accountId`: bigint/string — reference to ledger account
  - `amount`: decimal/float — amount value
  - `eventType`: string — categorical classification
  - `createdAt`: `datetime_immutable` — UTC timestamp, default to `new \DateTimeImmutable()`
- **Immutability enforcement**:
  - All fields `private`
  - Constructor accepts all required fields; `createdAt` optional (defaults to UTC now)
  - Zero methods starting with `set`
  - Only getter methods: `getId()`, `getAccountId()`, `getAmount()`, `getEventType()`, `getCreatedAt()`

## Relationships
- None required by spec. FinancialLog is standalone audit record referencing `accountId` (not a formal Doctrine relation, matches spec's key-attribute description).

## Validation Rules (from Requirements)
- FR-001: private fields for id, accountId, amount, eventType, createdAt — ENFORCED
- FR-002: NO setter methods — ENFORCED (compile/code inspection)
- FR-003: constructor-only initialization; `createdAt` defaults to current UTC — ENFORCED
- FR-004: getter methods for all fields — ENFORCED
- FR-005: PHPUnit integration test uses `symfony_app` DB role — ENFORCED
- FR-006: integration test inserts row then attempts UPDATE — ENFORCED
- FR-007: UPDATE must throw database exception (permission denied) — ENFORCED
- FR-008: `symfony_app` role has SELECT and INSERT — ENFORCED (via existing 002 config)

## State Transitions
- None (immutable; no state machine).
