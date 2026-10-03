-- Create custom administration and application roles
CREATE ROLE migration_admin WITH LOGIN PASSWORD 'admin_secure_password' SUPERUSER;
CREATE ROLE symfony_app WITH LOGIN PASSWORD 'app_secure_password';

-- Grant core connection permissions to the financial ledger database
GRANT CONNECT ON DATABASE financial_ledger_test TO symfony_app;

-- Lock down all tables in the public schema zone by default
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT ON TABLES TO symfony_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE UPDATE, DELETE ON TABLES FROM symfony_app;

GRANT USAGE ON SCHEMA public TO symfony_app;
