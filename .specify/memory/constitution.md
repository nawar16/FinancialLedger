# Project Constitution

## Mission

To provide a tamper‑evident, append‑only cryptographic ledger for financial documents built with Symfony, ensuring data integrity, security, and auditability.

## Core Principles

### I. Security‑First Ledger Design

All financial transactions must be cryptographically signed, immutable, and auditable. Symfony security components (firewall, encryption, validation) must be rigorously applied to prevent tampering and unauthorized access. The ledger must enforce append‑only writes and cryptographic chaining of entries to guarantee an immutable history.

### II. Testing Reliability and CI/CD Excellence

Every line of code must be covered by automated tests (unit, integration, functional) that run in CI on every commit. Acceptance tests must define business rules and be written in Gherkin or similar syntax. The CI/CD pipeline must include security scanning, dependency checks, zero‑downtime deployment, and automated rollback. Local Docker environments are discouraged for development to avoid environment drift; use Symfony's built‑in server and test against production‑parallel staging environments instead.

## Development Workflow

- Tests written using `phpunit` (unit) and `behat` (acceptance) MUST pass
- All user stories MUST have corresponding acceptance tests

## Quality Gates

### Pre-Commit

- Symfony coding standards (PHPCS) and security scan MUST pass
- All tests MUST pass

## Governance

This constitution supersedes all other practices. Amendments require documentation.

**Version**: 1.1.0 | **Ratified**: 2026-09-30 | **Last Amended**: 2026-09-30
