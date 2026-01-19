# **PeopleSync — Employee Attendance System (MVP)**

PeopleSync is a mobile & web-based employee attendance application designed as an HRIS MVP with a focus on data accuracy, auditability, and backend scalability.

---

## 🚀 Key Features

### ✅ Implemented (Admin Panel - Filament v4)

#### Kelola Karyawan (Employee Management)
| Menu | Description | Status |
|------|-------------|--------|
| Data Karyawan | Employee CRUD with department assignment, soft delete support | ✅ |

#### Kelola Kehadiran (Attendance Management)
| Menu | Description | Status |
|------|-------------|--------|
| Kehadiran Harian | Daily attendance with manual entry, late/early detection | ✅ |
| Review Kehadiran (Harian) | Daily attendance review with permission-based access | ✅ |
| Review Kehadiran (Bulanan) | Monthly attendance review/recap | ✅ |
| Hari Libur | Holiday management for attendance calculation | ✅ |
| Jadwal Kerja | Work schedule per day of week (pre-seeded, edit only) | ✅ |
| Lokasi Kehadiran | Geofencing locations with interactive map & radius | ✅ |

#### Other Modules
| Menu | Description | Status |
|------|-------------|--------|
| Department | Department hierarchy with parent-child structure | ✅ |
| Leave | Leave/time-off requests with approval workflow | ✅ |
| Overtime | Overtime requests with approval workflow | ✅ |

#### Pengaturan (Settings)
| Menu | Description | Status |
|------|-------------|--------|
| Data Admin | Admin user management (role-based visibility) | ✅ |

### 🔜 Planned (Mobile App - Flutter)
| Feature | Description |
|---------|-------------|
| Clock-in / Clock-out | GPS & photo-based attendance via mobile |
| Attendance History | View personal attendance records |
| Leave Request | Submit leave/time-off requests |
| Overtime Request | Submit overtime requests |

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Flutter (Mobile / Web)                    │
│                         [Planned]                            │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│              Laravel 12 API (JWT + Spatie RBAC)             │
│                      [Implemented]                           │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                        PostgreSQL                            │
│                      [Implemented]                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Key Architectural Decisions

* **Stateless JWT authentication** — Token-based API auth
* **Server-side RBAC** — Roles & permissions resolved server-side using Spatie
* **Separation of User vs Employee** — User for auth, Employee for business data
* **Soft delete semantics** — With partial unique indexes for data integrity
* **Strong database constraints** — Over application-level trust
* **Geofencing support** — Haversine formula for location validation

---

## 🔐 Authentication & Authorization

| Aspect | Implementation |
|--------|----------------|
| Auth Method | JWT (tymon/jwt-auth) |
| RBAC | Spatie Laravel Permission |
| Role Resolution | Server-side (not embedded in token) |
| Soft-deleted Users | Treated as inactive |
| Admin Panel Auth | Filament built-in |

---

## 📊 Database Schema (Implemented)

### Core Tables
```
users ──────────┐
                ├── employees ─── departments (self-referential)
                │       │
                │       ├── attendances
                │       ├── leaves
                │       └── overtimes
                │
permission_tables (Spatie)
```

### Additional Tables
| Table | Purpose |
|-------|---------|
| `work_schedules` | Work hours per day of week |
| `holidays` | Public holidays for attendance calculation |
| `locations` | Geofencing with lat/lng/radius |

### Key Indexes & Constraints
```sql
-- Partial unique index for soft delete
CREATE UNIQUE INDEX users_email_unique ON users(email) WHERE deleted_at IS NULL;
CREATE UNIQUE INDEX employees_number_unique ON employees(employee_number) WHERE deleted_at IS NULL;

-- Attendance lookup optimization
CREATE INDEX attendances_employee_date ON attendances(employee_id, date);
```

---

## 🛠️ Tech Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Backend | Laravel | 12.x |
| Database | PostgreSQL | - |
| Auth (API) | JWT (tymon/jwt-auth) | 2.x |
| Auth (Admin) | Filament | 4.x |
| RBAC | Spatie Laravel Permission | 6.x |
| Admin UI | Filament | 4.x |
| Mobile | Flutter | Planned |
| Cache | Redis | Planned |

---

## ✨ Filament v4 Highlights

### Custom Components
- **MapPicker** — Interactive Leaflet map with radius picker for geofencing locations

### Features Used
- Modern Schema patterns (`live()`, `visible()`, dehydrate hooks)
- Navigation groups with Indonesian labels
- Permission-based menu visibility
- Soft delete handling in queries
- Custom table filters with raw PostgreSQL queries

---

## 🧮 Attendance Logic

### Attendance Record (State)
* One record per employee per day
* Tracks: `clock_in_at`, `clock_out_at`, `source`, `notes`
* Auto-calculated: `is_late`, `late_duration_minutes`, `is_early_leave`

### Source Tracking
| Source | Description |
|--------|-------------|
| `mobile` | Clock via mobile app (planned) |
| `manual` | Manual entry by HR/Admin |

### Status Types (for Review)
| Status | Label | Color |
|--------|-------|-------|
| Present | Hadir | Success |
| Absent | Tidak Hadir | Danger |
| On Leave | Cuti/Izin | Info |
| Weekend | Akhir Pekan | Gray |
| Holiday | Libur | Warning |
| Not Yet | Belum Tiba | Gray |

---

## 🗓️ Work Schedule System

- Pre-seeded 7-day schedule (Sunday = 0, Saturday = 6)
- Configurable: `is_working_day`, `work_start_time`, `work_end_time`
- Used for late/early leave calculation
- **Cannot create/delete** — Only edit existing schedules

---

## 📍 Geofencing (Location)

### Features
- Interactive map picker with search
- Configurable radius (10m - 5000m)
- Haversine formula for distance calculation
- Active/inactive status

### Methods Available
```php
$location->isWithinRadius($lat, $lng);     // Check if coordinate is within radius
$location->calculateDistance($lat, $lng);  // Get distance in meters
Location::findNearest($lat, $lng);         // Find nearest active location
Location::findContaining($lat, $lng);      // Find all locations containing point
```

---

## 📋 Roadmap

### ✅ MVP (Current - Implemented)
- [x] Authentication (JWT + Filament)
- [x] RBAC with Spatie Laravel Permission
- [x] Employee & Department management
- [x] Daily attendance with manual entry
- [x] Attendance review (daily & monthly)
- [x] Leave & overtime management with approval
- [x] Work schedule configuration
- [x] Holiday management
- [x] Geofencing locations with map picker

### 🔜 Next Phase
- [ ] Mobile app (Flutter) with clock-in/out
- [ ] Attendance Logs (immutable audit trail)
- [ ] Photo capture for attendance proof
- [ ] Push notifications

### 📅 Post-MVP
- [ ] Payroll integration
- [ ] Advanced shift scheduling
- [ ] Analytics dashboard
- [ ] Multi-tenant support
- [ ] Redis caching

---

## 💡 Why This Project?

This project demonstrates:

| Aspect | What It Shows |
|--------|---------------|
| **Backend Architecture** | Clean separation of concerns, service patterns |
| **Database Design** | PostgreSQL-specific features, proper indexing |
| **Security** | JWT + RBAC, server-side permission resolution |
| **Modern Stack** | Laravel 12 + Filament v4 latest patterns |
| **Business Logic** | Real-world HR/attendance rules |
| **Custom Components** | Filament custom form components (MapPicker) |

---

## 📁 Project Structure

```
people-sync/
├── backend/
│   ├── app/
│   │   ├── Enums/              # AttendanceSource, AttendanceStatus, DayOfWeek
│   │   ├── Filament/
│   │   │   ├── Forms/Components/   # Custom components (MapPicker)
│   │   │   └── Resources/          # Resource modules
│   │   │       ├── Attendances/
│   │   │       ├── Departments/
│   │   │       ├── Employees/
│   │   │       ├── Holidays/
│   │   │       ├── Leaves/
│   │   │       ├── Locations/
│   │   │       ├── Overtimes/
│   │   │       ├── Users/
│   │   │       └── WorkSchedules/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/migrations/
│   └── routes/
└── docs/
    └── v2/                     # PRD & migration specs
```

---

## 🏃 Getting Started

```bash
# Clone & setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Database
php artisan migrate
php artisan db:seed

# Development
composer dev

# Running project
php artisan serve

# Access Filament Admin Panel
# Default: http://localhost:8000/admin
```

---

## 📝 Final Note

PeopleSync is intentionally designed as a **production-minded MVP**, focusing on:
- ✅ **Correctness** — Strong data validation & constraints
- ✅ **Auditability** — Source tracking, soft deletes
- ✅ **Extensibility** — Ready for payroll, multi-tenant, and more

Rather than feature count, the priority is **real-world quality**.