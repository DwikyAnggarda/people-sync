# ✅ KEPUTUSAN FINAL (DIKUNCI)

* 🔐 **Authentication**: JWT (API) + Filament Auth (Admin Panel)
* 🗄️ **Database Schema**: MVP Absensi — **SELESAI**
* ✅ **RBAC**: Spatie Laravel Permission — **SELESAI**
* ✅ **Geofencing**: Locations dengan MapPicker — **SELESAI**
* ❌ Payroll, queue, redis → **ditunda ke Post-MVP**

---

# 🧠 Prinsip Desain Database (Pegangan)

Sebelum tabel, ini aturan mainnya:

* **Soft delete** di semua data utama
* **Tidak over-normalization**
* **Query-friendly untuk mobile**
* **RBAC di level user** (via Spatie Laravel Permission)
* **Siap dikembangkan (tanpa rewrite)**

---

# 🧱 ERD KONSEPTUAL (IMPLEMENTED)

```
users ──────────────────┐
        │               │
        ├── employees ──┼── departments (self-referential)
        │       │       │
        │       ├── attendances ←── work_schedules
        │       ├── leaves          holidays
        │       └── overtimes       locations
        │
permission_tables (Spatie) ──┘
```

---

# 📦 DATABASE SCHEMA FINAL (DETAIL)

## 1️⃣ `users`

Digunakan untuk **auth (JWT & Filament)**

```sql
users
- id (uuid, pk)
- name
- email (unique, partial index)
- password
- email_verified_at (nullable)
- remember_token (nullable)
- created_at
- updated_at
- deleted_at
```

🔹 Catatan:

* Email **unique WHERE deleted_at IS NULL**
* User ≠ Employee (dipisah, ini BENAR)
* UUID untuk user identity

---

## 2️⃣ Permission Tables (Spatie Laravel Permission) ✅

> Menggunakan **spatie/laravel-permission** package, bukan custom tables

```sql
roles
- id (pk)
- name
- guard_name
- created_at
- updated_at

permissions
- id (pk)
- name
- guard_name
- created_at
- updated_at

model_has_roles
- role_id (fk)
- model_type
- model_id (uuid untuk users)

model_has_permissions
- permission_id (fk)
- model_type
- model_id (uuid untuk users)

role_has_permissions
- permission_id (fk)
- role_id (fk)
```

🔹 Catatan:

* `model_id` menggunakan UUID (bukan BIGINT) karena users.id = UUID
* Guard: `web` untuk Filament Admin

---

## 3️⃣ `employees`

Data karyawan (core business)

```sql
employees
- id (pk, bigint)
- user_id (nullable, fk -> users.id)
- employee_number (unique, partial index)
- name
- email
- department_id (fk)
- status (active, inactive)
- joined_at
- created_at
- updated_at
- deleted_at
```

🔹 `user_id` nullable → **employee bisa belum punya akun**
🔹 Email di sini **bukan auth**, hanya data

---

## 4️⃣ `departments`

Struktur organisasi (hierarki)

```sql
departments
- id (pk)
- name
- parent_id (nullable, self fk)
- created_at
- updated_at
- deleted_at
```

---

## 5️⃣ `attendances` ⭐ (CORE)

Clock in / clock out

```sql
attendances
- id (pk)
- employee_id (fk)
- date (yyyy-mm-dd)
- clock_in_at (timestamp)
- clock_out_at (nullable, timestamp)
- photo_path (nullable)
- latitude (nullable, decimal 10,7)
- longitude (nullable, decimal 10,7)
- source (enum: mobile, manual)
- notes (nullable, text)          -- ✅ ADDED
- created_at
- updated_at
```

### Constraint penting (WAJIB):

* 1 employee **tidak boleh double clock-in** dalam 1 hari

### Computed Attributes (di Model):
* `is_late` — Cek keterlambatan berdasarkan work_schedules
* `late_duration_minutes` — Durasi terlambat dalam menit
* `is_early_leave` — Cek pulang awal
* `work_duration_minutes` — Total durasi kerja

---

## 6️⃣ `work_schedules` ✅ (IMPLEMENTED)

Jadwal kerja per hari

```sql
work_schedules
- id (pk)
- day_of_week (int: 0=Sunday, 6=Saturday)
- is_working_day (boolean)
- work_start_time (time)
- work_end_time (time)
- created_at
- updated_at
```

🔹 Pre-seeded 7 records (Minggu-Sabtu)
🔹 Tidak bisa create/delete, hanya edit

---

## 7️⃣ `holidays` ✅ (IMPLEMENTED)

Hari libur nasional/perusahaan

```sql
holidays
- id (pk)
- name
- date (date)
- description (nullable)
- created_at
- updated_at
```

---

## 8️⃣ `locations` ✅ (IMPLEMENTED - GEOFENCING)

Lokasi untuk validasi kehadiran

```sql
locations
- id (pk)
- name
- latitude (decimal 10,8)
- longitude (decimal 10,8)
- radius_meters (int)
- address (nullable, text)
- is_active (boolean, default true)
- created_at
- updated_at
```

🔹 Menggunakan **Haversine formula** untuk kalkulasi jarak
🔹 Custom **MapPicker component** di Filament dengan Leaflet

### Methods Available:
```php
$location->isWithinRadius($lat, $lng);     // Check if within radius
$location->calculateDistance($lat, $lng);  // Distance in meters
Location::findNearest($lat, $lng);         // Find nearest active
Location::findContaining($lat, $lng);      // Find all containing point
```

---

## 9️⃣ `leaves`

Izin / cuti

```sql
leaves
- id (pk)
- employee_id (fk)
- type (annual, sick, permission, unpaid)
- start_date
- end_date
- reason (nullable)
- status (pending, approved, rejected)
- approved_by (fk -> users.id, nullable)
- created_at
- updated_at
- deleted_at
```

---

## 🔟 `overtimes`

Lembur

```sql
overtimes
- id (pk)
- employee_id (fk)
- date
- start_time
- end_time
- reason (nullable)
- status (pending, approved, rejected)
- approved_by (fk -> users.id, nullable)
- created_at
- updated_at
- deleted_at
```

---

# 🎨 ENUMS (IMPLEMENTED)

```php
// AttendanceSource
enum AttendanceSource: string {
    case Mobile = 'mobile';
    case Manual = 'manual';
}

// AttendanceStatus (untuk Review)
enum AttendanceStatus: string {
    case Present = 'present';
    case Absent = 'absent';
    case OnLeave = 'on_leave';
    case Weekend = 'weekend';
    case Holiday = 'holiday';
    case NotYet = 'not_yet';
}

// DayOfWeek
enum DayOfWeek: int {
    case Sunday = 0;
    case Monday = 1;
    // ... sampai Saturday = 6
}
```

---

# 🔐 JWT DESIGN (SESUAI DB)

### JWT Payload (REKOMENDASI)

```json
{
  "sub": "uuid-user-id",
  "roles": ["employee"],
  "employee_id": 10,
  "exp": 1710000000
}
```

🔹 `sub` menggunakan UUID (bukan integer)
🔹 `employee_id` disisipkan → **mobile tidak perlu extra call**

---

# 📈 INDEX STRATEGY (IMPLEMENTED)

```sql
-- users
CREATE UNIQUE INDEX users_email_unique
ON users(email)
WHERE deleted_at IS NULL;

-- employees
CREATE UNIQUE INDEX employees_number_unique
ON employees(employee_number)
WHERE deleted_at IS NULL;

-- attendances
CREATE INDEX attendances_employee_date
ON attendances(employee_id, date);

-- work_schedules
CREATE UNIQUE INDEX work_schedules_day_unique
ON work_schedules(day_of_week);
```

---

# ✅ YANG SUDAH DIIMPLEMENTASI

| Feature | Status | Catatan |
| ------- | ------ | ------- |
| Users & Auth | ✅ | JWT + Filament Auth |
| RBAC | ✅ | Spatie Laravel Permission |
| Employees | ✅ | CRUD + Soft Delete |
| Departments | ✅ | Hierarki parent-child |
| Attendances | ✅ | Daily + Manual entry |
| Work Schedules | ✅ | 7-day configuration |
| Holidays | ✅ | Holiday management |
| Locations/Geofencing | ✅ | MapPicker + Haversine |
| Leaves | ✅ | Approval workflow |
| Overtimes | ✅ | Approval workflow |

---

# ❌ YANG DITUNDA (POST-MVP)

| Tidak Ada | Kenapa |
| --------- | ------ |
| payroll tables | Scope Post-MVP |
| audit_logs / attendance_logs | Bisa ditambah nanti |
| refresh_token table | JWT simple dulu |
| advanced shifts | Per-employee schedule |
| multi-tenant | Future SaaS |

👉 **Ini bukan kekurangan, tapi fokus.**

---

# 🧠 VALIDASI SENIOR-LEVEL

Dengan schema ini:

* Filament v4 CRUD → **lancar**
* API Flutter → **simple**
* JWT → **ringkas**
* Geofencing → **siap pakai**
* Mudah extend ke:
  * payroll
  * multi-tenant
  * audit log
  * advanced scheduling