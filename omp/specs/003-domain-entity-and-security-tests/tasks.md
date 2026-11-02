---
description: "Task list for feature: Immutable FinancialLog Entity with Database Security Tests"
---

# Tasks: Immutable FinancialLog Domain Entity with Database Security Tests

**Feature Branch**: `feature/domain-entity-and-security-tests`  
**Spec**: `specs/003-domain-entity-and-security-tests/spec.md`  
**Input Documents**: `plan.md`, `spec.md`, `data-model.md`, `research.md`, `quickstart.md`, `checklists/requirements.md`

**Project Type**: Symfony 7 web-service (`backend/` + `frontend/`)  
**Language/Version**: PHP 8.1+ / Symfony 7.x  
**Storage**: PostgreSQL (Doctrine ORM)  
**Testing**: PHPUnit (`vendor/bin/phpunit`)

---

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Parallelizable (different files, no cross-dependencies)
- **[US1] / [US2]**: User story phase label (P1 stories)
- Paths relative to repo root (`backend/src/Entity/`, `backend/tests/integration/`)

---

## Phase 1: Setup (Shared Infrastructure) — Blocked by prerequisites

- [ ] T001 Verify environment prerequisites (`.specify/scripts/bash/check-prerequisites.sh --json`; `FEATURE_DIR` = `specs/003-domain-entity-and-security-tests`)
- [ ] T002 Confirm `backend/config/packages/doctrine.yaml` has `naming_strategy: underscore` and `App` mapping pointing to `%kernel.project_dir%/src/Entity`
- [ ] T003 Confirm `financial_logs` table exists with columns `id`, `accountId`, `amount`, `eventType`, `createdAt` (from feature 002 schema / assumption in spec.md)

---

## Phase 2: Foundational (Blocking Prerequisites — MUST complete before any user story)

**Checkpoint**: Foundation ready — user story work can begin in parallel.

- [ ] T004 Verify `symfony_app` database role exists with INSERT + SELECT on `financial_logs`; UPDATE / DELETE revoked (feature 002 security config; reference `backend/src/Entity/Role.php` or DB grants)
- [ ] T005 Confirm PHPUnit integration-test bootstrap connects via `DATABASE_URL` using `symfony_app` credentials (test DB config / `.env.test` / `phpunit.xml.dist`)
- [ ] T006 Create integration-test base class / bootstrap in `backend/tests/integration/` for DB-level assertions (empty dir currently — establish test scaffolding)

---

## Phase 3: User Story 1 — Immutable FinancialLog Domain Entity (P1) 🎯 MVP

**Goal**: Build `FinancialLog` Doctrine entity with constructor-only initialization, zero setter methods, all private fields, getters for all fields; `createdAt` defaults to current UTC when omitted.

**Independent Test**: Instantiate `new FinancialLog(...)`, verify getters return correct values, confirm `createdAt` is UTC `DateTimeImmutable`; scan class for zero methods starting with `set`.

### Tests for User Story 1 (requested — write first, expect failure before fix)

- [ ] T007 [P] [US1] Zero-setter validation scan: write script / test that asserts `get_class_methods(FinancialLog::class)` contains zero `set*` methods (`backend/src/Entity/FinancialLog.php`)
- [ ] T008 [P] [US1] Constructor initialization test: assert all fields set via constructor; `createdAt` defaults to `new \DateTimeImmutable()` UTC (`backend/src/Entity/FinancialLog.php`)

### Implementation for User Story 1

- [ ] T009 [US1] Implement `FinancialLog` entity fields (`id`, `accountId`, `amount`, `eventType`, `createdAt`) with `#[ORM\Column(...)]` mappings; table `financial_logs` (`backend/src/Entity/FinancialLog.php`)
- [ ] T010 [US1] Implement constructor accepting all required fields; `createdAt` optional with `\DateTimeImmutable()` UTC default (`backend/src/Entity/FinancialLog.php`)
- [ ] T011 [US1] Implement getter methods `getId()`, `getAccountId()`, `getAmount()`, `getEventType()`, `getCreatedAt()` — read-only access only (`backend/src/Entity/FinancialLog.php`)
- [ ] T012 [US1] Verify no setter methods exist (compile / static-analysis / reflection check); confirm immutability (`backend/src/Entity/FinancialLog.php`)
- [ ] T013 [US1] Update existing stub `backend/src/Entity/FinancialLog.php` (currently only `id` + getter) to full immutable spec — replace stub entirely

**Checkpoint**: User Story 1 complete — `FinancialLog` is immutable, has getters only, `createdAt` defaults to UTC.

---

## Phase 4: User Story 2 — PHPUnit Integration Test for Database Security (P1)

**Goal**: Integration test connects as `symfony_app`, performs SELECT and INSERT successfully on `financial_logs`, attempts UPDATE, asserts database exception thrown (permission denied / insufficient privilege).

**Independent Test**: Run `vendor/bin/phpunit backend/tests/integration/` — SELECT/INSERT pass; UPDATE throws `PDOException` (or DBAL exception) indicating privilege failure.

### Tests for User Story 2 (requested — TDD: write failing test first)

- [ ] T014 [P] [US2] Write integration test scaffold: `backend/tests/integration/FinancialLogSecurityTest.php` connecting with `symfony_app` role credentials
- [ ] T015 [P] [US2] Write SELECT + INSERT success assertions (`SELECT id FROM financial_logs ...`; `INSERT INTO financial_logs ...`)
- [ ] T016 [US2] Write UPDATE attempt + exception assertion (`UPDATE financial_logs SET amount = ... WHERE id = ...` must throw DB exception; assert exception type / message contains permission / privilege / not allowed)

### Implementation for User Story 2

- [ ] T017 [US2] Configure PHPUnit DB connection to `symfony_app` role (verify `.env.test` / `DATABASE_URL` / `phpunit.xml.dist` DB config points to role with SELECT/INSERT only)
- [ ] T018 [US2] Implement integration test `backend/tests/integration/FinancialLogSecurityTest.php`: insert row (INSERT succeeds), select row (SELECT succeeds), attempt UPDATE (assert exception thrown in 100% executions — `SC-004`)
- [ ] T019 [US2] Run integration test against live PostgreSQL (`vendor/bin/phpunit backend/tests/integration/FinancialLogSecurityTest.php`) — confirm failure of UPDATE due to DB-level restriction (not code-level)
- [ ] T020 [US2] Verify `symfony_app` role privileges: SELECT and INSERT work; UPDATE denied (reference feature 002 grants / `backend/src/Entity/Role.php` security setup)

**Checkpoint**: User Story 2 complete — DB-level security verified; `symfony_app` cannot modify `financial_logs`.

---

## Phase 5: Polish & Cross-Cutting Concerns

- [ ] T021 [P] Verify `backend/src/Entity/FinancialLog.php` passes zero-setter scan (`SC-001`)
- [ ] T022 [P] Verify `createdAt` defaults to UTC and getter returns `\DateTimeImmutable` (`SC-002`)
- [ ] T023 [P] Confirm `symfony_app` SELECT/INSERT pass; UPDATE throws DB exception (`SC-003`, `SC-004`)
- [ ] T024 Confirm `FinancialLog` immutable after construction — no modification via reflection, direct access (if any public property) blocked (`SC-005`)
- [ ] T025 Update `specs/003-domain-entity-and-security-tests/checklists/requirements.md` — mark FR-001 through FR-008; verify security/compliance items checked
- [ ] T026 Update `AGENTS.md` or `CLAUDE.md` if new dependency / pattern added (none expected; uses existing Symfony + Doctrine + PHPUnit)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — can start when foundation done; independent of US2
- **US2 (Phase 4)**: Depends on Phase 2 + US1 (entity must exist for DB-level test to insert/select a valid record — though insert could use raw SQL; for consistency, complete US1 first); can run in parallel with US1 test-writing (`T014`) but full execution needs entity table / mapping ready
- **Polish (Phase 5)**: Depends on US1 + US2 complete

### User Story Dependencies

- **US1 (P1)**: No external story dependencies — only requires Phase 2 (DB schema / role config verified)
- **US2 (P1)**: Requires US1 entity definition for meaningful integration (insert/select actual `FinancialLog` records); can begin scaffold/test-writing in parallel with US1 implementation but execution needs entity in place

### Within Each User Story

- US1: Tests (`T007`, `T008`) first → Implementation (`T009`–`T013`) → Verification (`T012`)
- US2: Tests (`T014`–`T016`) first → Config (`T017`) → Implementation (`T018`) → Execution (`T019`) → Verification (`T020`)

### Parallel Opportunities

- `T007`, `T008` (US1 tests) run in parallel with `T014`, `T015`, `T016` (US2 tests) once Phase 2 done
- `T009`, `T010` (entity fields / constructor) can be parallel; `T011` (getters) depends on `T009`, `T010`
- `T017` (DB config) can run parallel with `T009`–`T011`
- `T018` / `T019` execution depends on `T009`–`T011` + `T017`

---

## Parallel Example: User Story 1 + 2 Test Writing (Phase 2 complete)

```bash
# Launch US1 test scan and US2 test scaffold together:
Task "T007 [P] [US1] Zero-setter validation scan" -> backend/src/Entity/FinancialLog.php
Task "T014 [P] [US2] Write integration test scaffold" -> backend/tests/integration/FinancialLogSecurityTest.php
```

---

## Implementation Strategy

### MVP First (User Story 1 Only — Stop after Phase 3 + polish check T021–T024)

1. Phase 1 → Phase 2
2. Phase 3 (US1): Implement `FinancialLog` entity, verify zero setters, confirm UTC default
3. Stop and validate: `SC-001`, `SC-002`, `SC-005`

### Incremental Delivery (US1 then US2)

1. Phase 1 + Phase 2 → Foundation ready
2. US1 → Test independently (`SC-001`, `SC-002`) → Deploy/Demo
3. US2 → Test independently (`SC-003`, `SC-004`) → Confirm DB-level restriction works
4. Polish (`SC-005`) → Feature complete

---

## Verification Checkpoints (from spec.md Success Criteria)

| Checkpoint | Task(s) | Evidence |
|---|---|---|
| SC-001 Zero setters | T007, T012 | Reflection / `get_class_methods()` count = 0 `set*` |
| SC-002 Constructor + UTC default + getters | T008, T009–T011 | `new FinancialLog(...); $log->getCreatedAt()->format('c')` is UTC |
| SC-003 SELECT + INSERT succeed as `symfony_app` | T015, T017, T018 | PHPUnit assertions pass |
| SC-004 UPDATE throws DB exception (100%) | T016, T018, T019 | `expectException` / DB error message contains privilege/permission |
| SC-005 Immutable after construction | T012, T024 | Reflection set fails / no mutable interface |

---

*Generated by `/speckit.tasks` (speckit.tasks.agent.md) for `specs/003-domain-entity-and-security-tests/`.*  
*Tasks follow checklist format (`- [ ] [ID] [P?] [US?] Description with path`). All tasks have exact file paths (`backend/src/Entity/FinancialLog.php`, `backend/tests/integration/FinancialLogSecurityTest.php`).*
