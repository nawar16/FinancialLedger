# Phase 0: Research & Technical Decisions
- **Target Topologies**: PostgreSQL 16 Role Segmentation
- **Admin Target Account**: `migration_admin` (Full DDL execution rights)
- **Application Target Account**: `symfony_app` (Data-only access; explicit `UPDATE` and `DELETE` blocking)
- **Log Footprint Structure**: Bigserial Primary Keys, explicit `timestamp_utc` time zone layouts, and dynamic transactional `JSONB` parameters.
