# 1. **Overview**

PeopleSync adalah aplikasi **HRIS ringan** yang menyediakan fitur inti HR:

* Manajemen karyawan
* Absensi (termasuk offline sync via PWA)
* Manajemen cuti
* Sistem penggajian (Payroll) berbasis job queue
* Notifikasi
* Audit log
* Settings organisasi

Aplikasi ini terdiri dari:

* **PWA (Employee App)** untuk karyawan (clock-in/out, cuti, notifikasi, payslip).
* **Admin Web (HR Portal)** untuk HR/Payroll Admin/Superadmin.

Backend dibangun dengan arsitektur:
Nuxt (PWA & Admin Web) → API Gateway → Laravel API → Redis (cache + queue) → Workers → PostgreSQL + S3.

---

# 2. **Purpose & Goals**

MVP HRIS dengan fokus:

* Sistem absensi yang presisi dan kompatibel dengan mobile offline.
* Sistem payroll sederhana namun production-ready (async, snapshot, reporting).
* Role-based access untuk employee, manager, HR, payroll admin, superadmin.
* Cepat untuk di-deploy, modular, fokus backend yang scalable.

# 3. **User Types & Roles**

### **3.1 Personas**

1. **Employee**

   * Clock-in/out
   * Offline sync
   * Ajukan cuti
   * Lihat payslip
   * Lihat notifikasi

2. **Manager**

   * Dapat melihat anggota departemen
   * Approve/reject cuti tim
   * Melihat attendance tim

3. **HR**

   * CRUD karyawan
   * CRUD departemen
   * Approve/reject cuti
   * Kelola salary components
   * Lihat activity logs

4. **Payroll Admin**

   * Generate payroll
   * Review payroll items
   * Akses payroll snapshots/reports

5. **Superadmin**

   * Akses penuh
   * Manage users/roles
   * Manage settings
   * Full audit log

6. **Auditor** (optional read-only role)

   * Akses read-only payroll snapshots & activity logs

---

# 4. **Product Scope (MVP)**

### **4.1 Core Modules**

1. **Authentication & RBAC**
2. **Employee Management**
3. **Department Management**
4. **Attendance Management**
5. **Leave Management**
6. **Payroll System (Async Worker Driven)**
7. **Salary Components**
8. **Payroll Snapshots & Reports**
9. **Notifications**
10. **Activity Logs**
11. **Settings**
12. **File Upload (Presigned URL)**

### **4.2 Non-functional requirements**

* Block N+1 queries (include/expand)
* Asynchronous heavy process (queue)
* Soft deletes + partial unique index
* Redis caching untuk read-heavy sections
* S3 storage untuk foto absensi
* Materialized view untuk laporan payroll

---

# 5. **Detailed Feature Requirements**

## 5.1 Authentication & Authorization

* Login menggunakan email + password → JWT.
* JWT berisi:

  * `sub` (user id)
  * `roles` (array)
  * `org_id` (future multi-tenant extension)
* Endpoint:

  * `POST /auth/login`
  * `GET /auth/me`
* Role enforcement:

  * menggunakan `x-allowed-roles` (dirumuskan dalam OpenAPI)
* Password disimpan hashed (bcrypt/argon2)

---

## 5.2 User & Role Management

### Features:

* Buat user baru (HR / Superadmin)
* Assign roles (hr, employee, manager, payroll_admin, superadmin)
* Soft delete user
* Tidak boleh punya duplicate email yang belum deleted
* User bisa dikaitkan dengan employee (optional)

### Special Rules:

* Superadmin hanya dibuat manual via seeder
* Email unique → partial index: `WHERE deleted_at IS NULL`

---

## 5.3 Department Management

* Departemen mendukung **hierarchy (parent_id)**.
* HR & Superadmin dapat CRUD.
* Employee/manager hanya bisa view.
* Digunakan oleh features employee filtering & payroll reporting.

---

## 5.4 Employee Management

### Features

* CRUD employee (HR/Superadmin)
* Soft delete
* Filtering:

  * department_id
  * status
  * search by name/email/employee_number
* Include:

  * department
  * latest_payroll

### Special Fields

* `employee_number` unique (partial index)
* `current_salary` (denormalized)
* `latest_payroll_id` (denormalized)

---

## 5.5 Attendance Management (Clock-in/out + Offline Sync)

### Features

* Clock-in / Clock-out (PWA)
* Attendance offline mode:

  * Record saved locally in PWA
  * Sync ke backend → `POST /attendances/sync`
  * Worker → memproses batch, dedupe berdasar client_id
* Photo upload via S3 presigned URL
* Location (lat,lng) optional
* Source: `mobile`, `pwa`, `web`

### Business rules

* 1 karyawan tidak boleh clock-in dua kali tanpa clock-out (validation worker/API)
* Attendance tidak dihapus (jika soft delete dipakai, hanya HR dapat melakukannya)
* Idempotency:

  * gunakan `client_id`
  * atau header `Idempotency-Key`

---

## 5.6 Leave Management

### Features

* Tipe cuti:

  * annual
  * sick
  * permission
  * unpaid
* Employee dapat create leave.
* Manager dapat approve leave untuk tim-nya.
* HR dan Superadmin dapat approve semua leave.

### Business rules:

* `approved_by` disimpan untuk audit
* `days` dihitung oleh backend (optional)
* Soft delete berlaku untuk revisi kesalahan manajemen

---

## 5.7 Salary Components

### Features:

* CRUD salary components:

  * earning
  * deduction
* Optional default_amount
* Biasanya digunakan untuk payroll formula builder

---

## 5.8 Payroll Generation (Asynchronous)

### **Core MVP Feature**

* Endpoint:
  **`POST /payrolls` → always returns 202 Accepted + job_id**
* Worker menghitung payroll:

  * fetch employees
  * fetch salary_components
  * hitung gross & net
  * simpan payroll_items
  * upsert payroll_snapshots
  * update employees.latest_payroll_id
* Snapshots digunakan untuk reporting cepat

### Status job:

* queued → processing → done → failed
* endpoint: `GET /jobs/{job_id}`

### Materialized View

* `mv_payroll_by_department`
* refresh by worker/cron

---

## 5.9 Payroll Snapshots

* Disimpan setiap payroll posting
* Query cepat untuk UI dashboard HR

---

## 5.10 Notifications

* Notifikasi disimpan di table `notifications`

* Use cases:

  * payroll finished
  * leave approved
  * attendance sync imported

* Employee/Admin dapat membaca notifikasi

* Soft delete untuk admin cleanup

---

## 5.11 Activity Logs

* Mencatat semua tindakan sensitif:

  * employee update
  * salary changes
  * leave approve/reject
  * settings update

* Format:

  ```
  user_id  
  action  
  resource_type  
  resource_id  
  meta (jsonb)
  created_at
  deleted_at (optional)
  ```

---

## 5.12 Settings

* Org-level settings:

  * payroll_cutoff_day
  * timezone
  * org_name
* Hanya admin & superadmin yang boleh update

---

## 5.13 File Upload (S3 Presigned URL)

### Flow:

1. Client call → `POST /uploads/presign`
2. API return `upload_url` + `file_url`
3. Client upload langsung ke S3
4. file_url disimpan ke DB

---

# 6. **Technical Requirements**

## 6.1 Backend

* Laravel 10+
* PostgreSQL 14+
* Redis (Queue + Cache)
* Workers (Horizon / Supervisor)
* Docker-ready

## 6.2 API Design

* OpenAPI 3.0
* Pagination model (page/per_page)
* `include` for relations
* Idempotency Key for critical endpoints
* Role-based metadata: `x-allowed-roles`

## 6.3 Database Design

### Soft Delete

* All major mutable tables have `deleted_at`
* Partial unique indexes:

  * employees(employee_number)
  * users(email)

### Async Jobs Tables

* payroll_jobs
* attendance_import_jobs

### Materialized View

* mv_payroll_by_department

---

# 7. **Security Requirements**

* JWT-based authentication (short-lived token)
* Role-based access (RBAC)
* Strong audit logging
* Upload security (file type, size)
* Rate limiting at API gateway
* All sensitive modifications logged

---

# 8. **Performance Requirements**

* Attendance syncing must handle 100–1000 records per batch
* Payroll generation must not lock tables → done via workers
* Query employees with include must avoid N+1 → use eager loading
* Redis caching for:

  * departments
  * salary components
* Payroll snapshots used instead of heavy JOIN queries

---

# 9. **MVP Success Criteria**

1. Employee dapat:

   * clock-in/out
   * offline attendance sync
   * ajukan cuti
   * lihat payslip

2. HR dapat:

   * CRUD employee
   * CRUD department
   * Approve leaves
   * Generate payroll

3. Payroll admin dapat:

   * generate payroll
   * akses payroll snapshot

4. Superadmin dapat:

   * manage roles/users
   * manage settings
   * melihat activity logs

5. Semua fitur berjalan dengan performa stabil:

   * attendance sync <1s response average (202 accepted)
   * payroll job berjalan di worker tanpa block
   * dashboard payroll load <300ms

---

# 10. **Roadmap (3 Tahapan)**

### MVP

✔ Auth
✔ Employees
✔ Departments
✔ Attendance (clock-in/out + sync)
✔ Leave management
✔ Payroll generate (async)
✔ Notifications
✔ Audit log
✔ Settings
✔ Soft delete
✔ Materialized views

### Post-MVP

* Payslip PDF generation
* HR dashboard analytics
* Manager dashboard
* Employee self-update modules
* Webhook events (attendance, payroll)

### Advanced / SaaS version

* Multi-tenant
* PTO balance (cuti tahunan otomatis)
* Shift scheduling
* Overtime rules
* Loan & benefits
* Device binding & geofencing

---

# 11. **Appendix**

## 11.1 Architecture Diagram

* PWA + Admin → API Gateway → Laravel API → Redis → Worker → PostgreSQL / S3

## 11.2 API Schema

* [peoplesync.apidog.io](https://peoplesync.apidog.io/)

---