# Tasks: 002-schema-and-roles

**Input**: Design from `/specs/002-schema-and-roles/`
**Prerequisites**: `spec.md`, `plan.md`, `research.md`, `data-model.md`
**Status**: Checklists complete (requirements.md all [x])

---

## Phase 1: Setup & Foundational

- [ ] T001 Create Symfony 7 project structure (`src/`, `migrations/`, `tests/`)
- [ ] T002 Configure Doctrine ORM 3.x + Migrations 4.x (`config/packages/doctrine.yaml`)
- [ ] T003 Configure PostgreSQL connection for `symfony_app` / `migration_admin`
- [ ] T004 [P] Set up PHPUnit 11 + Behat 4 test infrastructure

**Checkpoint**: Foundation ready — DB roles and entities can be created

---

## Phase 2: User Story 1 — Complete Database Schema (P1) 🎯 MVP

**Goal**: Define all core tables (`users`, `roles`, `role_permissions`, `user_roles`, `categories`, `ledger_accounts`, `transactions`, `ledger_entries`, `audit_log`) with FKs and double-entry integrity.

### Tests (first — must fail)
- [ ] T010 [P] [US1] `tests/unit/MigrationTest.php` — fresh DB migration + idempotency (SC-001, SC-006)
- [ ] T011 [P] [US1] `tests/unit/DoubleEntryTest.php` — debit/credit mismatch rejected (SC-002)

### Implementation
- [ ] T012 [US1] Migration `Version20261001000001.php` — `users`, `roles`, `role_permissions`, `user_roles`
- [ ] T013 [US1] Migration `Version20261001000002.php` — `categories`, `ledger_accounts`, `transactions`, `ledger_entries`
- [ ] T014 [US1] Migration `Version20261001000003.php` — `audit_log`
- [ ] T015 [US1] Entities `src/Entity/User.php`, `Role.php`, `RolePermission.php`, `UserRole.php`
- [ ] T016 [US1] Entities `src/Entity/Category.php`, `LedgerAccount.php`
- [ ] T017 [US1] Entities `src/Entity/Transaction.php`, `LedgerEntry.php`, `AuditLog.php`
- [ ] T018 [US1] Add FK constraints (`ledger_entries.transaction_id → transactions.id`; `ledger_entries.ledger_account_id → ledger_accounts.id`; `transactions.category_id → categories.id`)
- [ ] T019 [US1] Enforce double-entry at DB level (trigger/constraint that `SUM(debit) = SUM(credit)` per `transaction_id`)
- [ ] T020 [US1] Add indexes (`ledger_entries.ledger_account_id`, `ledger_entries.created_at`) (FR-010)
- [ ] T021 [US1] Soft-delete `is_active`/`deleted_at` on `categories` and `ledger_accounts` (FR-011)
- [ ] T022 [US1] Add `version` / `updated_at` to `ledger_accounts`, `transactions` (FR-009)

**Checkpoint**: All core tables exist; double-entry constraint holds; balance queries <200ms

---

## Phase 3: User Story 2 — Application-Level Roles & Permissions (P1)

**Goal**: `roles`, `role_permissions`, `user_roles` mappings; roles `admin`, `accountant`, `user`, `viewer`; permissions stored in DB (FR-005, FR-006).

- [ ] T030 [US2] Seed roles + permissions in migrations / fixtures
- [ ] T031 [P] [US2] `tests/unit/RoleEnforcementTest.php` — viewer denied create; admin has all (SC-004)
- [ ] T032 [US2] `src/Security/RoleVoter.php` (reference for later auth feature; schema only here)

---

## Phase 4: User Story 3 — DB-Level User Roles (P2)

**Goal**: `ledger_readonly`, `ledger_writer`, `ledger_admin` with least-privilege grants (FR-007).

- [ ] T040 [US3] Migration `Version20261001000004.php` — create DB roles + grants
- [ ] T041 [P] [US3] `tests/integration/RoleEnforcementTest.php` — `ledger_readonly` INSERT denied; `ledger_writer` CREATE TABLE denied; `ledger_admin` DDL allowed (SC-005)

---

## Phase 5: User Story 5 — Connection Topologies (P1)

**Goal**: `migration_admin` (DDL) vs `symfony_app` (data-only); `symfony_app` revoked UPDATE/DELETE on public schema; `migration_admin` has USAGE + CREATE (FR-012, FR-013).

- [ ] T050 [US5] Migration `Version20261001000005.php` — `migration_admin` + `symfony_app` roles + privilege grants/revokes
- [ ] T051 [P] [US5] `tests/integration/ConnectionTopologyTest.php` — symfony_app ALTER TABLE denied; migration_admin CREATE succeeds; symfony_app UPDATE/DELETE revoked (SC-009)

---

## Phase 6: User Story 6 — Immutable Financial Logs (P2)

**Goal**: `financial_logs` (`BIGSERIAL`, `timestamp_utc`, `event_type`, `payload` JSONB); append-only; UPDATE/DELETE revoked (FR-014).

- [ ] T060 [US6] Migration `Version20261001000006.php` — `financial_logs` table
- [ ] T061 [US6] `src/Entity/FinancialLog.php`
- [ ] T062 [P] [US6] `tests/acceptance/FinancialLogTest.php` — INSERT OK; UPDATE/DELETE rejected; JSONB @> query works (SC-010)
- [ ] T063 [US6] Revoke UPDATE/DELETE from all roles on `financial_logs`

---

## Phase 7: Polish & Validation

- [ ] T070 Run all migrations fresh (`php bin/console doctrine:migrations:migrate`) — SC-001
- [ ] T071 Re-run migrations — SC-006 idempotency
- [ ] T072 Rollback 1 step — SC-007
- [ ] T073 Run acceptance tests (`behat`) — US1-US6
- [ ] T074 Verify `quickstart.md` command executes
- [ ] T075 Update `CLAUDE.md`; close feature

---

## Dependencies

- **Phase 1** → blocks Phase 2–6
- **US1 (Phase 2)** → blocks US2 integration (but US2 schema can proceed in parallel with US1 entities)
- **US5 (Phase 5)** → depends on US1 schema existing; can run after Phase 2
- **US6 (Phase 6)** → independent of US3/US5; can run after Phase 2
- **Tests (T010, T011)** → must be written first; fail before T012–T022
