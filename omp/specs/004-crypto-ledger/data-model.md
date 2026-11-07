# Data Model: Crypto-Ledger

## Entity: FinancialLog

| Field | Type | Description | Constraints |
|-------|------|-------------|-------------|
| `id` | UUID | Unique identifier for the ledger entry | Primary key, auto‑generated |
| `accountId` | string | Identifier of the financial account | Not null |
| `amount` | Decimal | Monetary value of the transaction | Not null, fixed‑decimal representation |
| `eventType` | string | Type of event (e.g., 'deposit', 'withdrawal', 'transfer') | Not null |
| `createdAt` | DateTimeImmutable | Timestamp of entry creation | Defaults to current UTC time if omitted |

## Relationships

- **One‑to‑Many**: A `FinancialLog` belongs to exactly one `Account` (implicitly defined by `accountId`).
- **Append‑Only**: Once created, a `FinancialLog` cannot be modified; only new entries are added.

## Serialization

- The `CanonicalSerializer` produces a deterministic JSON representation:
  - Keys sorted alphabetically.
  - Numeric values converted to fixed‑decimal strings (no floating‑point).
  - Dates converted to ISO‑8601 format.
- The serialization is used for storage, audit trails, and cross‑service communication.

## Storage

- Table: `financial_logs` (PostgreSQL, Doctrine ORM)
- Columns: `id` (UUID), `accountId` (string), `amount` (decimal), `eventType` (string), `createdAt` (DateTimeImmutable)
- The `previous_hash` field is **not** stored in the database; it is computed in memory during chain verification.

## Constraints

- All entries must be immutable after creation.
- The `createdAt` field must always be present and set to UTC.
- The `amount` field must be a fixed‑decimal value (no floating‑point rounding).
