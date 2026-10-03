# Implementation Plan: Database Schema and Role Design for Symfony Financial Ledger

**Branch**: `002-schema-and-roles` | **Date**: 2026-10-03 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/002-schema-and-roles/spec.md`

## Summary

Define a complete, versioned PostgreSQL schema for a Symfony financial ledger including users, roles, permissions, categories, ledger accounts, transactions, double-entry ledger entries, audit log, and an immutable financial_logs table. Establish application-level roles (admin, accountant, user, viewer) with granular permissions, and database-level connection topologies separating migration_admin (DDL) from symfony_app (data access). All changes delivered as idempotent, versioned migrations with rollback paths.

## Technical Context

**Language/Version**: PHP 8.1+  
**Primary Dependencies**: Symfony 7.x, Doctrine ORM 3.x, Doctrine Migrations 4.x  
**Storage**: PostgreSQL 15+ (JSONB required for financial_logs and audit_log)  
**Testing**: phpunit 11 (unit/integration), behat 4 (acceptance/Gherkin)  
**Target Platform**: Linux server  
**Project Type**: web-service (Symfony application)  
**Performance Goals**: Balance query <200ms for 100k ledger entries; schema migration <30s on fresh DB  
**Constraints**: Append-only financial_logs (no UPDATE/DELETE); least-privilege DB roles; idempotent migrations  
**Scale/Scope**: Multiple ledger accounts per user; unbounded ledger_entries; multi-currency out of scope (v1)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
|------|--------|-------|
| Security‑First Ledger Design | PASS | Financial events immutably recorded; DB-level append-only enforcement on financial_logs |
| Testing Reliability & CI/CD | PASS | PHPUnit for unit/integration, Behat for Gherkin acceptance tests (defined in US scenarios) |
| Symfony Coding Standards (PHPCS) | PASS | No source code in this feature; PHPCS applies to PHP files in subsequent features |
| No environment drift | N/A | No Docker dev environment; uses Symfony built-in server + production-parallel staging |
| No unjustified complexity violations | PASS | No Repository pattern violations; schema is single-project Doctrine entity model |

## Project Structure

### Documentation (this feature)

```text
specs/002-schema-and-roles/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
# This feature adds only schema definitions; source code structure follows Symfony 7 conventions:

src/
├── Entity/
│   ├── User.php
│   ├── Role.php
│   ├── RolePermission.php
│   ├── UserRole.php
│   ├── Category.php
│   ├── LedgerAccount.php
│   ├── Transaction.php
│   ├── LedgerEntry.php
│   ├── AuditLog.php
│   └── FinancialLog.php
├── Security/
│   └── (auth enforcement for US2; defined in subsequent feature)
└── (no other source directories in this feature)

# Migrations
migrations/
├── Version20261001000001.php   # US1: users, roles, role_permissions, user_roles
├── Version20261001000002.php   # US1: categories, ledger_accounts, transactions, ledger_entries
├── Version20261001000003.php   # US1: audit_log
├── Version20261001000004.php   # US5: DB-level roles (ledger_readonly, ledger_writer, ledger_admin)
├── Version20261001000005.php   # US5: connection topologies (migration_admin, symfony_app)
├── Version20261001000006.php   # US6: financial_logs table

# Tests
tests/
├── unit/
│   ├── MigrationTest.php       # SC-001, SC-006: fresh migration + idempotency
│   ├── DoubleEntryTest.php     # SC-002: debit/credit integrity enforcement
│   └── BalanceQueryTest.php    # SC-003: <200ms for 100k entries
├── integration/
│   ├── RoleEnforcementTest.php # SC-004, SC-005: DB role permission denied tests
│   └── ConnectionTopologyTest.php # SC-009: symfony_app vs migration_admin
└── acceptance/
    ├── FeatureTest.php         # Behat/Gherkin: US1-US6 acceptance scenarios
    └── FinancialLogTest.php    # SC-010: financial_logs INSERT/UPDATE/DELETE/JSONB
```

**Structure Decision**: Single-project Symfony application following standard Symfony 7 layout (Entity/, Security/ directories). Schema is split across 6 ordered migrations for atomicity and clear rollback paths. No frontend or mobile components — this feature is backend-only.

## Complexity Tracking

> **No violations identified** — all constitution gates pass without justification.
