-- PeopleSync PostgreSQL DDL (v4) - N+1 aware + soft deletes + partial unique indexes
-- Requires pgcrypto for gen_random_uuid() (or change to uuid-ossp uuid_generate_v4()).

CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- roles
CREATE TABLE IF NOT EXISTS roles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name varchar(100) UNIQUE NOT NULL,
  description text,
  created_at timestamptz DEFAULT now()
);

-- departments (self-referencing parent_id)
CREATE TABLE IF NOT EXISTS departments (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name varchar(150) NOT NULL,
  code varchar(50),
  parent_id uuid,
  created_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_departments_parent FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- employees
CREATE TABLE IF NOT EXISTS employees (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_number varchar(50) NOT NULL,
  first_name varchar(150) NOT NULL,
  last_name varchar(150),
  email varchar(255),
  phone varchar(50),
  department_id uuid,
  hired_at date,
  status varchar(50) DEFAULT 'active',
  current_salary numeric(38,2),
  latest_payroll_id uuid,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_employees_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- users
CREATE TABLE IF NOT EXISTS users (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  employee_id uuid,
  is_active boolean DEFAULT true,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_users_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
);

-- user_roles
CREATE TABLE IF NOT EXISTS user_roles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  role_id uuid NOT NULL,
  created_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- salary_components
CREATE TABLE IF NOT EXISTS salary_components (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name varchar(150) NOT NULL,
  type varchar(50) NOT NULL,
  default_amount numeric(38,2) DEFAULT 0,
  created_at timestamptz DEFAULT now(),
  deleted_at timestamptz
);

-- payrolls (kept as hard records)
CREATE TABLE IF NOT EXISTS payrolls (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_id uuid NOT NULL,
  period_year int NOT NULL,
  period_month int NOT NULL,
  gross_amount numeric(38,2) DEFAULT 0,
  net_amount numeric(38,2) DEFAULT 0,
  status varchar(50) DEFAULT 'draft',
  generated_at timestamptz,
  paid_at timestamptz,
  created_at timestamptz DEFAULT now(),
  CONSTRAINT fk_payrolls_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT uq_payrolls_employee_period UNIQUE (employee_id, period_year, period_month)
);

-- payroll_items (hard records)
CREATE TABLE IF NOT EXISTS payroll_items (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  payroll_id uuid NOT NULL,
  component_id uuid NOT NULL,
  amount numeric(38,2) NOT NULL,
  note text,
  created_at timestamptz DEFAULT now(),
  CONSTRAINT fk_payrollitems_payroll FOREIGN KEY (payroll_id) REFERENCES payrolls(id) ON DELETE CASCADE,
  CONSTRAINT fk_payrollitems_component FOREIGN KEY (component_id) REFERENCES salary_components(id)
);

-- attendances (soft-delete)
CREATE TABLE IF NOT EXISTS attendances (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_id uuid NOT NULL,
  clock_in timestamptz NOT NULL,
  clock_out timestamptz,
  clock_in_client_ts timestamptz,
  clock_out_client_ts timestamptz,
  lat numeric(10,7),
  lng numeric(10,7),
  location varchar(255),
  location_accuracy numeric(8,3),
  device_id varchar(255),
  photo_url varchar(1024),
  source varchar(50),
  sync_status varchar(50) DEFAULT 'pending',
  created_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_attendances_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- leaves (soft-delete)
CREATE TABLE IF NOT EXISTS leaves (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_id uuid NOT NULL,
  leave_type varchar(100),
  start_date date NOT NULL,
  end_date date NOT NULL,
  days numeric(6,2),
  status varchar(50) DEFAULT 'pending',
  approved_by uuid,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_leaves_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  CONSTRAINT fk_leaves_approvedby FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- payroll_snapshots
CREATE TABLE IF NOT EXISTS payroll_snapshots (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_id uuid NOT NULL,
  period_year int NOT NULL,
  period_month int NOT NULL,
  gross_amount numeric(38,2),
  net_amount numeric(38,2),
  created_at timestamptz DEFAULT now(),
  CONSTRAINT uq_payrollsnapshots_employee_period UNIQUE (employee_id, period_year, period_month),
  CONSTRAINT fk_snapshots_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- activity_logs (soft-delete optional)
CREATE TABLE IF NOT EXISTS activity_logs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid,
  action varchar(255) NOT NULL,
  resource_type varchar(100),
  resource_id uuid,
  meta jsonb,
  created_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_activitylogs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- notifications (soft-delete)
CREATE TABLE IF NOT EXISTS notifications (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL,
  title varchar(255),
  message text,
  is_read boolean DEFAULT false,
  sent_at timestamptz,
  created_at timestamptz DEFAULT now(),
  deleted_at timestamptz,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- settings (keep as single record; no soft-delete)
CREATE TABLE IF NOT EXISTS settings (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  org_name varchar(255),
  payroll_cutoff_day int DEFAULT 25,
  timezone varchar(100) DEFAULT 'Asia/Jakarta',
  created_at timestamptz DEFAULT now()
);

-- Optional job tables for async workflows (kept as hard records)
CREATE TABLE IF NOT EXISTS payroll_jobs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid,
  payload jsonb,
  status text DEFAULT 'queued',
  result jsonb,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

CREATE TABLE IF NOT EXISTS attendance_import_jobs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid,
  payload jsonb,
  status text DEFAULT 'queued',
  result jsonb,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Indexes (including partial unique indexes to support soft deletes)

-- employees: unique employee_number only for non-deleted rows
CREATE UNIQUE INDEX IF NOT EXISTS uq_employees_employee_number_not_deleted
  ON employees(employee_number)
  WHERE deleted_at IS NULL;

-- users: unique email only for non-deleted rows
CREATE UNIQUE INDEX IF NOT EXISTS uq_users_email_not_deleted
  ON users(email)
  WHERE deleted_at IS NULL;

-- Additional useful indexes
CREATE INDEX IF NOT EXISTS idx_employees_department ON employees(department_id);
CREATE INDEX IF NOT EXISTS idx_employees_latest_payroll ON employees(latest_payroll_id);
CREATE INDEX IF NOT EXISTS idx_employees_dept_updated ON employees(department_id, updated_at DESC);
CREATE INDEX IF NOT EXISTS idx_attendances_employee_created_at ON attendances(employee_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_payrollitems_payroll ON payroll_items(payroll_id);
CREATE INDEX IF NOT EXISTS idx_activitylogs_user_created_at ON activity_logs(user_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_payrolls_generated_at ON payrolls(generated_at);
CREATE INDEX IF NOT EXISTS idx_payrollsnapshots_period ON payroll_snapshots(period_year, period_month);

-- Materialized view for payroll by department (read-only view refreshed periodically)
CREATE MATERIALIZED VIEW IF NOT EXISTS mv_payroll_by_department AS
SELECT e.department_id, ps.period_year, ps.period_month, SUM(ps.net_amount) total_net
FROM payroll_snapshots ps
JOIN employees e ON ps.employee_id = e.id
GROUP BY e.department_id, ps.period_year, ps.period_month;

CREATE INDEX IF NOT EXISTS idx_mv_payroll_dept ON mv_payroll_by_department(department_id, period_year, period_month);

-- NOTES:
-- 1) To permanently remove soft-deleted records, run a purge job:
--    DELETE FROM employees WHERE deleted_at IS NOT NULL AND deleted_at < now() - INTERVAL '90 days';
-- 2) Application-layer: implement soft delete by setting deleted_at = now() (and optionally deleted_by).
-- 3) Queries should always include "WHERE deleted_at IS NULL" for tables with soft delete, or use a DB view that filters it.
-- 4) For ORMs like Eloquent (Laravel), enable SoftDeletes trait to auto-handle filtered queries.

