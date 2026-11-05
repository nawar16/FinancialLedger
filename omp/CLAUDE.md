# omp Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-10-03

## Active Technologies

- PHP 8.1+ + Symfony 7.x, Doctrine ORM 3.x, Doctrine Migrations 4.x (002-schema-and-roles)



## Commands

# Add commands for PHP 8.1+

## Code Style

PHP 8.1+: Follow standard conventions

## Recent Changes

- 002-schema-and-roles: Added PHP 8.1+ + Symfony 7.x, Doctrine ORM 3.x, Doctrine Migrations 4.x

<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->


## Architectural Constraints & Directory Structure
- **Strict DDD Layout:** All domain logic, entities, and repositories MUST live inside bounded contexts under `src/Domain/[Context]/Entity/` and `src/Domain/[Context]/Repository/`.
- **Prohibited:** Never create or use root-level `src/Entity/`, `src/Repository/`, or `src/Controller/` directories. Symfony maker-bundle commands must be scoped to the appropriate domain path.
