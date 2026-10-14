# Phase 0: Research — 003-domain-entity-and-security-tests

**Feature**: Immutable FinancialLog Entity with Database Security Tests
**Branch**: feature/domain-entity-and-security-tests
**Spec**: specs/003-domain-entity-and-security-tests/spec.md

## Decisions

- Decision: Implement FinancialLog as Doctrine ORM entity with constructor-only initialization and zero setter methods.
- Rationale: Feature spec (FR-002, FR-003) requires absolute immutability for audit integrity; constructor-only fields enforce this at compile/code level.
- Alternatives considered: Setter + validation logic (rejected: allows mutation); Value object pattern (rejected: feature requires entity mapping to `financial_logs` table for DB-level security test).

- Decision: Use `symfony_app` DB role for PHPUnit integration test; assert UPDATE throws Postgres permission exception.
- Rationale: Spec (FR-005–FR-007, SC-004) explicitly requires database-level privilege verification against the `symfony_app` role configured in feature 002.
- Alternatives considered: Mocking DB layer (rejected: must test actual Postgres privileges); using admin role (rejected: would not verify role restriction).

- Decision: Default `createdAt` to `new \DateTimeImmutable()` in UTC.
- Rationale: Spec (FR-003) requires UTC default; `DateTimeImmutable` avoids accidental mutation.

## Rationale

Repository uses Symfony 7 + Doctrine ORM 3 + PHPUnit (CLAUDE.md; backend/src/Entity/User.php pattern). Existing `users` table and `symfony_app` role are from feature 002; `financial_logs` table is assumed per spec assumptions. No new dependencies required.

## Unknowns Resolved (NEEDS CLARIFICATION → resolved)

- Language/Version: PHP 8.1+ / Symfony 7.x (from repo / CLAUDE.md) — RESOLVED
- Primary Dependencies: Symfony, Doctrine ORM, PHPUnit — RESOLVED
- Storage: PostgreSQL (doctrine.yaml + spec assumption) — RESOLVED
- Testing: PHPUnit integration tests (backend/tests/integration/) — RESOLVED
- Target Platform: Linux server / Docker backend environment — RESOLVED
- Project Type: Symfony web-service (backend + frontend) — RESOLVED
- Performance Goals: Not domain-critical; entity is small audit record — RESOLVED
- Constraints: NO setters; DB role must deny UPDATE — RESOLVED
- Scale/Scope: Single domain entity + one integration test — RESOLVED
