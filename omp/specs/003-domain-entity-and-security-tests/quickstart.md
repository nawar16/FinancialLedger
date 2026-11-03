# Quickstart — 003-domain-entity-and-security-tests

## Goal
Build immutable `FinancialLog` Symfony entity and PHPUnit integration test for DB-level security.

## Setup (using existing environment)
- PHP 8.1+ / Symfony 7.x backend
- Doctrine ORM + Postgres (from feature 002)
- PHPUnit available (`vendor/bin/phpunit` or Symfony test runner)

## Key Commands
```bash
# Create entity file
# backend/src/Entity/FinancialLog.php

# Run integration tests
php vendor/bin/phpunit backend/tests/integration/
```

## Verification Steps
1. Inspect `FinancialLog.php` — confirm zero `set`-prefixed methods.
2. Instantiate via constructor (`new FinancialLog(...)`) and call getters.
3. Confirm `createdAt` defaults to current UTC when omitted.
4. Run integration test connecting as `symfony_app` — INSERT succeeds, SELECT succeeds, UPDATE throws DB exception.
