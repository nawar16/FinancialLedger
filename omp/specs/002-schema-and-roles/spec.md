# Feature Specification: Database Schema and Role Design for Symfony Financial Ledger

**Feature Branch**: `002-schema-and-roles`
**Created**: 2026-10-02
**Status**: Draft
**Input**: User description: "db-schema-and-roles"

## User Scenarios & Testing *(mandatory)*

<!--
  User stories are prioritized as user journeys ordered by importance.
  Each story is independently testable: implementing just ONE delivers a viable slice of value.
-->

### User Story 1 - Define Complete Database Schema for Ledger Operations (Priority: P1)

As a database administrator or developer, I want a complete, versioned database schema that defines all tables required for financial ledger operations so that the application can store accounts, transactions, ledger entries, and balances with data integrity guaranteed.

**Why this priority**: Without a correct schema, no financial data can be persisted or retrieved. Every other feature (transactions, reporting, reconciliation) depends on this being in place first.

**Independent Test**: Apply all migrations to a fresh database instance and verify that all tables exist with correct column types, constraints, and indexes. Run a representative data-insertion test (create account → create category → create transaction → verify ledger entries created) against the migrated schema to confirm integrity constraints hold.

**Acceptance Scenarios**:

1. **Given** a fresh empty database, **When** all schema migrations are applied in order, **Then** all required tables exist with correct column types, primary keys, foreign keys, and indexes, and no migration errors are raised.
2. **Given** an account, a category, and a new transaction (debit/credit) exist, **When** a transaction is inserted with a missing or invalid category reference, **Then** the database rejects the insert with a foreign key violation.
3. **Given** the ledger_entries table uses double-entry bookkeeping where every transaction must have matching debit and credit entries, **When** a transaction is inserted with mismatched debit and credit amounts, **Then** a database constraint or trigger rejects the row.
4. **Given** any ledger account, **When** a balance query is run, **Then** the returned balance equals the sum of all corresponding ledger entries for that account.

---

### User Story 2 - Define Application-Level Roles and Permissions (Priority: P1)

As a system designer, I want clearly defined application-level roles (e.g., Admin, Accountant, Viewer, User) with granular permissions so that different users interact with the financial ledger with appropriate access control.

**Why this priority**: Access control is a core security requirement for any financial system. Role design must be established before building authentication-protected endpoints, as every subsequent feature will reference these roles.

**Independent Test**: Define the role entities and permission mappings in the schema. Verify that a test user assigned the "Viewer" role can read ledger entries but cannot create or modify them, while an "Accountant" role user can create and edit transactions but cannot manage user roles.

**Acceptance Scenarios**:

1. **Given** the roles table and role-permission mapping are defined, **When** a user is assigned the "viewer" role, **Then** that user's effective permissions include read access to all ledger data but exclude create, update, or delete permissions.
2. **Given** the roles table includes an "admin" role, **When** a user is assigned the "admin" role, **Then** that user's effective permissions include all permissions including user and role management.
3. **Given** two users with different roles, **When** both users access the same resource, **Then** access is granted or denied based on the permissions associated with each user's role, not on user identity directly.

---

### User Story 3 - Define Database-Level User Roles (Priority: P2)

As a database administrator, I want database-level user roles (e.g., `ledger_readonly`, `ledger_writer`, `ledger_admin`) with scoped permissions so that application database connections use least-privilege access and sensitive data is protected even if an application-level bypass occurs.

**Why this priority**: Defense-in-depth for a financial system. Application-level role enforcement is the primary control, but DB-level roles provide a second layer. This is P2 because it operates at a different layer and can be refined after the application schema is stable.

**Independent Test**: Create the database-level roles and assign appropriate grants. Verify that a connection using `ledger_readonly` can execute SELECT statements but receives an access-denied error on INSERT/UPDATE/DELETE. Verify that `ledger_writer` can insert and update transactions but cannot alter schema or manage users.

**Acceptance Scenarios**:

1. **Given** the `ledger_readonly` DB role has been granted SELECT on all ledger tables, **When** a session using that role executes an INSERT statement, **Then** the database returns a permission denied error.
2. **Given** the `ledger_writer` DB role has been granted SELECT, INSERT, and UPDATE on transaction and ledger_entry tables, **When** a session using that role executes a CREATE TABLE or ALTER TABLE statement, **Then** the database returns a permission denied error.
3. **Given** the `ledger_admin` DB role, **When** a session using that role executes DDL (CREATE, ALTER, DROP) statements, **Then** the statements succeed.

---

### User Story 4 - Ensure Schema Migrations Are Versioned and Repeatable (Priority: P2)

As a developer, I want all schema changes delivered as versioned, idempotent migrations so that any environment (dev, test, staging, production) can be brought to the latest schema state deterministically.

**Why this priority**: Versioned migrations prevent schema drift between environments and enable safe rollback. Without this, deploying schema changes becomes a manual, error-prone process.

**Independent Test**: Run all migrations on a fresh database and verify the final state matches the expected schema. Run the migrations a second time and confirm they are idempotent (no errors, no changes). Run a rollback one version back and verify the previous schema state is restored.

**Acceptance Scenarios**:

1. **Given** a fresh database with no migrations applied, **When** all migrations are executed sequentially, **Then** the database schema matches the latest version with zero errors.
2. **Given** a database already at the latest schema version, **When** migrations are run again, **Then** no changes are made and no errors are raised (idempotency).
3. **Given** a database at the latest schema version, **When** one rollback step is executed, **Then** the schema reverts to the previous version without data loss in tables that persist across versions.

---

### User Story 5 - Map Database Connection Topologies for migration_admin and symfony_app Roles (Priority: P1)

As a database administrator, I want a defined connection topology that separates the migration_admin role (with DDL and schema-migration privileges) from the symfony_app role (data read/write only) so that application database connections operate with least privilege and cannot accidentally or maliciously alter schema.

**Why this priority**: Database roles that can modify schema from within the application represent a critical security risk for a financial system. Separating migration from application access is a foundational control that must be designed before any data layer is deployed.

**Independent Test**: Connect to the database using the symfony_app role and confirm that DDL statements (CREATE TABLE, ALTER TABLE) are rejected with a permission-denied error. Connect using the migration_admin role and confirm that migrations execute successfully. Confirm that the symfony_app role has no UPDATE or DELETE privileges on the public schema.

**Acceptance Scenarios**:

1. **Given** the `migration_admin` DB role is granted CREATE and USAGE on the public schema, **When** a session using `migration_admin` executes a CREATE TABLE statement, **Then** the table is created successfully.
2. **Given** the `symfony_app` DB role is granted only CONNECT and USAGE on the public schema plus INSERT/SELECT on ledger tables, **When** a session using `symfony_app` executes an ALTER TABLE statement, **Then** the database returns a permission denied error.
3. **Given** UPDATE and DELETE privileges have been explicitly revoked from `symfony_app` on the public schema, **When** a session using `symfony_app` executes an UPDATE or DELETE statement on any table, **Then** the database returns a permission denied error and no rows are modified.
4. **Given** a new database connection is established for the application, **When** the connection string authenticates as `symfony_app`, **Then** the connection succeeds but cannot access any table outside the explicitly granted set of ledger tables.

---

### User Story 6 - Design financial_logs Table for Immutable Financial Event Audit Trail (Priority: P2)

As a compliance officer, I want an immutable financial_logs table with a BIGINT primary key, a UTC timestamp, a categorical event_type, and a JSONB payload so that all financial-affecting events are durably recorded with full context for audit and forensic analysis.

**Why this priority**: Financial regulations require an immutable audit trail of all events affecting financial data. While the broader audit_log table captures application-level mutations, financial_logs is a dedicated, append-only table specifically for financial events with structured searchability via JSONB.

**Independent Test**: Insert a set of financial events (transaction created, balance changed, account modified) into financial_logs. Query by event_type and verify only matching rows return. Query by a JSONB field within the payload and confirm filtering works. Attempt an UPDATE on an existing financial_logs row and confirm the database rejects it (append-only enforcement).

**Acceptance Scenarios**:

1. **Given** the financial_logs table is created with a `BIGSERIAL` primary key, `timestamp_utc` column with time zone, `event_type` column, and `payload` JSONB column, **When** a new financial event is inserted, **Then** a unique sequential ID is assigned and the row is stored with a UTC-normalized timestamp.
2. **Given** financial events of different event_types are stored in financial_logs, **When** a query filters by `event_type = 'transaction_created'`, **Then** only rows with that event_type are returned.
3. **Given** the payload column contains structured JSONB data, **When** a query uses a JSONB containment operator (e.g., payload @> '{"account_id": 42}'), **Then** rows matching the JSON criteria are returned efficiently.
4. **Given** the financial_logs table is append-only (UPDATE and DELETE revoked from all roles), **When** an attempt is made to UPDATE or DELETE an existing row, **Then** the database returns a permission denied error and the original row remains unchanged.

---

### Edge Cases

- What happens when a transaction references a category that is deleted concurrently? (Foreign key constraint must handle this; soft-delete categories are preferred.)
- How are zero-amount transactions handled? (Should be allowed for internal transfers but flagged for audit review.)
- What is the maximum number of ledger entries per transaction? (No hard limit, but index design must support efficient lookup.)
- How are concurrent balance updates handled to prevent race conditions? (Application-level locking or DB-level serializable transactions required; schema must support optimistic locking via a version column.)
- What happens if a migration fails mid-way on a large production database? (Migrations must be idempotent and atomic where possible; partial failure state must be recoverable.)

- How are connection topology changes handled during version upgrades? (Both roles should be recreated with correct privileges as part of migration scripts to prevent privilege drift.)
- What happens if the application attempts to use a role with excessive privileges? (Database-level restrictions prevent escalation even if application credentials are compromised.)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST define a complete database schema including (at minimum) the following tables: `users`, `roles`, `role_permissions`, `user_roles`, `categories`, `ledger_accounts`, `transactions`, `ledger_entries`, and `audit_log`.
- **FR-002**: System MUST enforce referential integrity via foreign key constraints on all relationships between `ledger_entries` and `transactions`, `ledger_entries` and `ledger_accounts`, and `transactions` and `categories`.
- **FR-003**: System MUST enforce double-entry bookkeeping at the database level: every transaction must have total debit amounts equal to total credit amounts, enforced via a database constraint or trigger.
- **FR-004**: System MUST support an `audit_log` table that records all create, update, and delete operations on ledger data, including the acting user, timestamp, and before/after values.
- **FR-005**: System MUST define application-level roles: `admin`, `accountant`, `user`, and `viewer`, each with a distinct set of permissions.
- **FR-006**: System MUST store role-permission mappings in the database (not hardcoded in application code) so that permissions can be adjusted without application redeployment.
- **FR-007**: System MUST define database-level user roles: `ledger_readonly`, `ledger_writer`, and `ledger_admin` with least-privilege grants.
- **FR-008**: System MUST deliver all schema changes as versioned, idempotent migrations with a corresponding rollback path for each migration step.
- **FR-009**: System MUST include a `version` or `updated_at` column on `ledger_accounts` and `transactions` tables to support optimistic concurrency control.
- **FR-010**: System MUST index `ledger_entries` by `ledger_account_id` and `created_at` to support efficient balance queries and time-range filtering.
- **FR-011**: System MUST support soft-delete on `categories` and `ledger_accounts` (via an `is_active` or `deleted_at` column) rather than hard deletion, to preserve historical integrity of ledger entries.

**FR-012**: System MUST define connection topologies for `migration_admin` and `symfony_app` DB roles with `migration_admin` having schema modification privileges and `symfony_app` having only data access privileges.
**FR-013**: System MUST revoke UPDATE and DELETE privileges from the `symfony_app` role on the `public` schema to enforce least-privilege access.
**FR-014**: System MUST create a `financial_logs` table with `BIGSERIAL` primary key, UTC timestamp, `event_type` column, and JSONB payload column for immutable financial event auditing.

### Key Entities

- **users**: Application users who can interact with the ledger. Key attributes: unique identifier, username, email, password hash, status, timestamps.
- **roles**: Named application-level access profiles. Key attributes: unique identifier, name, description, timestamps.
- **role_permissions**: Many-to-many mapping between roles and granular permissions. Key attributes: role_id, permission_code.
- **user_roles**: Many-to-many mapping between users and roles. Key attributes: user_id, role_id.
- **categories**: Classification labels for transactions (e.g., "Utilities", "Salary", "Rent"). Key attributes: unique identifier, name, type (income/expense/transfer), is_active, timestamps.
- **ledger_accounts**: Accounts that hold balances (e.g., "Checking", "Savings", "Credit Card"). Key attributes: unique identifier, name, account_type, currency, opening_balance, is_active, version, timestamps.
- **transactions**: A single financial event (income, expense, transfer). Key attributes: unique identifier, description, date, source_account_id, destination_account_id, category_id, total_amount, is_reconciled, version, timestamps.
- **ledger_entries**: Double-entry bookkeeping line items that make up a transaction. Each entry is a debit or credit against a specific ledger account. Key attributes: unique identifier, transaction_id, ledger_account_id, entry_type (debit/credit), amount, description, timestamps.
- **audit_log**: Immutable record of all data mutations. Key attributes: unique identifier, entity_type, entity_id, action (create/update/delete), acting_user_id, before_state (JSON), after_state (JSON), timestamp.

- **financial_logs**: Immutable append-only audit table for all financial events. Key attributes: unique `BIGSERIAL` primary key, `timestamp_utc` column (time zone, always UTC), `event_type` column (text, categorical classification such as transaction_created/balance_adjusted/ledger_entry_posted), and `payload` JSONB column (full structured context of the event). UPDATE and DELETE are revoked from all DB roles.
- **migration_admin**: Database-level role with CREATE and USAGE privileges on the public schema, used exclusively for running schema migrations; never used by the application connection.
- **symfony_app**: Database-level role used by the Symfony application connection with only CONNECT, USAGE, and INSERT/SELECT privileges on ledger tables; UPDATE and DELETE revoked on the public schema.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All schema migrations apply successfully to a fresh PostgreSQL/MySQL database instance in under 30 seconds with zero errors.
- **SC-002**: A double-entry integrity test (insert a transaction with mismatched debits/credits) is rejected by the database in 100% of test cases.
- **SC-003**: Balance query for a ledger account with 100,000 ledger entries completes in under 200 milliseconds using only the defined indexes.
- **SC-004**: Application-level role enforcement test: a "viewer" user attempting to create a transaction receives a permission denied response in 100% of test cases.
- **SC-005**: Database-level role enforcement test: a `ledger_readonly` session attempting an INSERT on `transactions` receives a permission denied error in 100% of test cases.
- **SC-006**: Running all migrations a second time on an already-migrated database produces no changes and no errors (idempotency verified in 100% of cases).
- **SC-007**: Rollback of the most recent migration step successfully restores the previous schema state without data loss in persistent tables.
- **SC-008**: All audit_log entries for a test session contain correct before/after state and acting user in 100% of test cases.

- **SC-009**: Connection topology test: the symfony_app role attempting to execute CREATE TABLE on the public schema receives a permission-denied error in 100% of test cases, while migration_admin role succeeds.
- **SC-010**: Financial_logs table: INSERT operations succeed in 100% of test cases, UPDATE/DELETE attempts are rejected with permission-denied errors, and JSONB payload queries return expected results efficiently.

## Assumptions

- The target database is a relational system (PostgreSQL or MySQL/MariaDB) compatible with Symfony's Doctrine ORM or equivalent migration tooling.
- The application uses standard Symfony conventions for entities, migrations, and security configuration.
- User authentication (credential storage, session management) is handled by an existing or separately specified feature; this feature defines the `users` table schema but does not implement auth logic.
- The `audit_log` table grows unboundedly over time; partitioning or archiving strategy is out of scope for this feature.
- Multiple simultaneous users may operate on the same ledger accounts; optimistic locking via a version column is the concurrency strategy assumed.
- Currency support is single-currency per ledger account for v1; multi-currency support (exchange rates, revaluation) is out of scope.
