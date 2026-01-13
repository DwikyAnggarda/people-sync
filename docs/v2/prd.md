# ✅ KEPUTUSAN FINAL (DIKUNCI)

* 🔐 **Authentication**: JWT
* 🗄️ **Fokus sekarang**: Finalisasi **Database Schema (MVP Absensi)**
* ❌ Payroll, queue, redis → **ditunda**

# 🧠 Prinsip Desain Database (Pegangan)

Sebelum tabel, ini aturan mainnya:

* **Soft delete** di semua data utama
* **Tidak over-normalization**
* **Query-friendly untuk mobile**
* **RBAC di level user**
* **Siap dikembangkan (tanpa rewrite)**

---

# 🧱 ERD KONSEPTUAL (MVP)

```
users ──┐
        ├── employees ─── departments
        │         │
        │         ├── attendances
        │         ├── leaves
        │         └── overtimes
roles ──┘
```

---

# 📦 DATABASE SCHEMA FINAL (DETAIL)

## 1️⃣ `users`

Digunakan untuk **auth (JWT)**

```sql
users
- id (pk)
- name
- email (unique, partial index)
- password
- created_at
- updated_at
- deleted_at
```

🔹 Catatan:

* Email **unique WHERE deleted_at IS NULL**
* User ≠ Employee (dipisah, ini BENAR)

---

## 2️⃣ `roles`

RBAC sederhana & jelas

```sql
roles
- id (pk)
- name (employee, admin, hr)
- created_at
```

---

## 3️⃣ `user_roles`

Pivot table

```sql
user_roles
- user_id (fk -> users.id)
- role_id (fk -> roles.id)

PRIMARY KEY (user_id, role_id)
```

---

## 4️⃣ `employees`

Data karyawan (core business)

```sql
employees
- id (pk)
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

## 5️⃣ `departments`

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

## 6️⃣ `attendances` ⭐ (CORE)

Clock in / clock out

```sql
attendances
- id (pk)
- employee_id (fk)
- clock_in_at
- clock_out_at (nullable)
- date (yyyy-mm-dd)
- photo_path (nullable)
- latitude (nullable)
- longitude (nullable)
- source (mobile, web)
- created_at
- updated_at
```

### Constraint penting (WAJIB):

* 1 employee **tidak boleh double clock-in** dalam 1 hari

Nanti enforce via:

* Validation logic
* (Optional) unique index `(employee_id, date)`

---

## 7️⃣ `leaves`

Izin / cuti

```sql
leaves
- id (pk)
- employee_id (fk)
- type (annual, sick, permission, unpaid)
- start_date
- end_date
- reason
- status (pending, approved, rejected)
- approved_by (fk -> users.id, nullable)
- created_at
- updated_at
- deleted_at
```

---

## 8️⃣ `overtimes`

Lembur

```sql
overtimes
- id (pk)
- employee_id (fk)
- date
- start_time
- end_time
- reason
- status (pending, approved, rejected)
- approved_by (fk -> users.id, nullable)
- created_at
- updated_at
- deleted_at
```

---

# 🔐 JWT DESIGN (SESUAI DB)

### JWT Payload (REKOMENDASI)

```json
{
  "sub": 1,
  "roles": ["employee"],
  "employee_id": 10,
  "exp": 1710000000
}
```

🔹 `employee_id` disisipkan → **mobile tidak perlu extra call**

---

# 📈 INDEX STRATEGY (WAJIB ADA)

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
```

---

# ❌ YANG SENGAJA TIDAK ADA

| Tidak Ada           | Kenapa              |
| ------------------- | ------------------- |
| payroll tables      | Scope MVP           |
| audit_logs          | Bisa ditambah nanti |
| refresh_token table | JWT simple          |
| shifts              | Advanced            |
| geofencing          | Post-MVP            |

👉 **Ini bukan kekurangan, tapi fokus.**

---

# 🧠 VALIDASI SENIOR-LEVEL

Dengan schema ini:

* Filament CRUD → **lancar**
* API Flutter → **simple**
* JWT → **ringkas**
* Mudah extend ke:

  * payroll
  * multi-tenant
  * audit log