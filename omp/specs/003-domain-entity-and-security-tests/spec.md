# Feature Specification: Immutable FinancialLog Entity with Database Security Tests

**Feature Branch**: `feature/domain-entity-and-security-tests`  
**Created**: 2026-10-14  
**Status**: Draft  
**Input**: User description: Build the immutable 'FinancialLog' Symfony domain entity ensuring it contains absolutely NO setter methods. Initialize the layout with fields: id, accountId, amount, eventType, and createdAt (defaulting to UTC). Configure a PHPUnit integration test suite that explicitly connects as the 'symfony_app' user database role. Rule: The integration test must intentionally attempt an UPDATE SQL statement on a 'FinancialLog' row and assert that a database exception is successfully thrown by Postgres.

## User Scenarios & Testing *(mandatory)*

<!--
  IMPORTANT: User stories should be PRIORITIZED as user journeys ordered by importance.
  Each user story/journey must be INDEPENDENTLY TESTABLE - meaning if you implement just ONE of them,
  you should still have a viable MVP (Minimum Viable Product) that delivers value.
  
  Assign priorities (P1, P2, P3, etc.) to each story, where P1 is the most critical.
  Think of each story as a standalone slice of functionality that can be:
  - Developed independently
  - Tested independently
  - Deployed independently
  - Demonstrated to users independently
-->

### User Story 1 - Immutable FinancialLog Domain Entity (Priority: P1)

As a backend developer, I want a FinancialLog domain entity that is completely immutable (no setter methods) with the required fields (id, accountId, amount, eventType, createdAt) so that once created, financial log entries cannot be modified, ensuring data integrity for audit trails.

**Why this priority**: Financial log entries must be immutable for regulatory compliance and audit integrity. This is P1 because it's a core requirement for any financial audit system and directly supports the security testing requirements.

**Independent Test**: Create a FinancialLog entity instance using constructor-only initialization, verify all fields are set correctly, and confirm that no setter methods exist on the class. Attempt to modify any field via reflection or direct property access (if public) and verify the design prevents modification.

**Acceptance Scenarios**:
1. **Given** the FinancialLog class definition, **When** examining the class methods, **Then** there are NO setter methods (methods starting with 'set') defined anywhere in the class.
2. **Given** required constructor parameters (id, accountId, amount, eventType, createdAt), **When** instantiating a new FinancialLog object, **Then** all fields are properly initialized and accessible via getter methods.
3. **Given** a FinancialLog instance, **When** attempting to modify any field through any means (setter, direct property access, reflection), **Then** the modification either fails or has no effect (immutable behavior).

### User Story 2 - PHPUnit Integration Test for Database Security (Priority: P1)

As a QA engineer, I want an integration test that verifies the database-level security by connecting as the 'symfony_app' role and attempting to update a FinancialLog record, expecting a database exception, so that I can confirm the application database role has appropriate restrictions.

**Why this priority**: Verifying database-level security is critical for a financial system. This is P1 because it validates that the symfony_app role cannot modify financial log data, which is essential for protecting audit trails from tampering.

**Independent Test**: Set up a test database connection using the symfony_app role credentials, insert a FinancialLog record, attempt to execute an UPDATE statement on that record, and assert that a database exception (specifically a permission denied or similar) is thrown.

**Acceptance Scenarios**:
1. **Given** a test database configured with symfony_app role having only INSERT/SELECT privileges on financial_logs table, **When** the integration test runs and attempts to UPDATE a financial_logs row, **Then** a database exception is thrown indicating insufficient privileges.
2. **Given** the same test database setup, **When** the integration test attempts a SELECT query on financial_logs table, **Then** the query executes successfully and returns expected results.
3. **Given** the same test database setup, **When** the integration test attempts an INSERT into financial_logs table, **Then** the insert executes successfully (verifying the role has required privileges for test setup).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a FinancialLog Symfony domain entity class with private fields for id, accountId, amount, eventType, and createdAt.
- **FR-002**: System MUST ensure the FinancialLog entity contains NO setter methods (no public methods beginning with 'set') to enforce immutability.
- **FR-003**: System MUST initialize the FinancialLog entity fields exclusively through its constructor, with createdAt defaulting to current UTC timestamp if not provided.
- **FR-004**: System MUST provide getter methods for all FinancialLog entity fields to allow read-only access to the immutable data.
- **FR-005**: System MUST configure a PHPUnit integration test suite that uses database connection credentials specifically for the 'symfony_app' database role.
- **FR-006**: System MUST include an integration test that inserts a FinancialLog record and then attempts an UPDATE SQL statement on that record.
- **FR-007**: System MUST assert that the UPDATE attempt in the integration test throws a database exception (indicating permission failure) when executed as the symfony_app role.
- **FR-008**: System MUST verify that the symfony_app database role has sufficient privileges to perform SELECT and INSERT operations on the financial_logs table for test setup and validation.

### Key Entities

- **FinancialLog**: Immutable domain entity representing a financial audit log entry. Key attributes: id (unique identifier), accountId (reference to ledger account), amount (decimal value), eventType (categorical classification of financial event), createdAt (timestamp of when the event occurred, UTC timezone). The entity enforces immutability through constructor-only initialization and absence of setter methods.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: FinancialLog entity class passes validation scan confirming ZERO setter methods exist in the class definition.
- **SC-002**: FinancialLog entity can be instantiated with all required fields and createdAt defaults to UTC when not explicitly provided, with all values accessible via getter methods.
- **SC-003**: PHPUnit integration test suite successfully connects to database using symfony_app role credentials and executes SELECT and INSERT operations on financial_logs table without errors.
- **SC-004**: PHPUnit integration test that attempts UPDATE on a financial_logs row throws a database exception in 100% of test executions when connected as symfony_app role.
- **SC-005**: FinancialLog entity maintains immutable state after instantiation - no field values can be modified through any accessible interface after construction.

## Assumptions

- The financial_logs database table exists with columns matching the FinancialLog entity fields (id, accountId, amount, eventType, createdAt) as defined in the schema from feature 002.
- The symfony_app database role has been configured with appropriate privileges (INSERT and SELECT on financial_logs table, but UPDATE/DELETE revoked) as specified in feature 002's security requirements.
- PHPUnit testing framework is available and configured for integration testing with database connectivity.
- UTC timezone handling is consistent across application and database layers for timestamp fields.
- The FinancialLog entity is intended for use as a read-only data transfer object or domain model, not for persistence operations directly (persistence handled via Doctrine ORM or similar).