# PeopleSync Mobile API Specification

> **Version**: 1.0.0  
> **Base URL**: `/api/v1`  
> **Authentication**: JWT Bearer Token

---

## 📋 Table of Contents

1. [Authentication](#1-authentication)
2. [Attendance](#2-attendance)
3. [Leave](#3-leave)
4. [Overtime](#4-overtime)
5. [Supporting Data](#5-supporting-data)
6. [Error Handling](#6-error-handling)

---

## 🔐 Authentication Header

All authenticated endpoints require:

```
Authorization: Bearer <jwt_token>
```

---

## 1. Authentication

### 1.1 Login

Authenticate user and receive JWT token.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/auth/login` |
| **Auth Required** | No |

#### Request Body

```json
{
  "email": "employee@example.com",
  "password": "password123"
}
```

#### Success Response (200)

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "employee@example.com"
    },
    "employee": {
      "id": 1,
      "employee_number": "EMP001",
      "name": "John Doe",
      "department": {
        "id": 1,
        "name": "Engineering"
      },
      "status": "active",
      "joined_at": "2024-01-15"
    }
  }
}
```

#### Error Response (401)

```json
{
  "success": false,
  "message": "Email atau password salah",
  "errors": null
}
```

---

### 1.2 Get Current User

Get authenticated user and employee data.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/auth/me` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": {
    "user": {
      "id": "uuid-string",
      "name": "John Doe",
      "email": "employee@example.com"
    },
    "employee": {
      "id": 1,
      "employee_number": "EMP001",
      "name": "John Doe",
      "department": {
        "id": 1,
        "name": "Engineering"
      },
      "status": "active",
      "joined_at": "2024-01-15"
    }
  }
}
```

---

### 1.3 Refresh Token

Refresh JWT token before expiration.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/auth/refresh` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Token berhasil diperbarui",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

### 1.4 Logout

Invalidate current JWT token.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/auth/logout` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null
}
```

---

## 2. Attendance

### 2.1 Get Today's Attendance

Check if user has clocked in/out today.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/attendances/today` |
| **Auth Required** | Yes |

#### Success Response (200) - Has Attendance

```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1,
    "date": "2026-01-20",
    "clock_in_at": "2026-01-20T08:05:00+07:00",
    "clock_out_at": null,
    "is_late": true,
    "late_duration_minutes": 5,
    "late_duration_formatted": "5 menit",
    "work_duration_minutes": null,
    "work_duration_formatted": "-",
    "source": "mobile",
    "can_clock_in": false,
    "can_clock_out": true
  }
}
```

#### Success Response (200) - No Attendance Yet

```json
{
  "success": true,
  "message": null,
  "data": {
    "date": "2026-01-20",
    "clock_in_at": null,
    "clock_out_at": null,
    "is_working_day": true,
    "is_holiday": false,
    "can_clock_in": true,
    "can_clock_out": false,
    "work_schedule": {
      "work_start_time": "08:00",
      "work_end_time": "17:00"
    }
  }
}
```

---

### 2.2 Clock In

Record clock in with GPS location and optional photo.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/attendances/clock-in` |
| **Auth Required** | Yes |
| **Content-Type** | `multipart/form-data` |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `latitude` | decimal | Yes | GPS latitude |
| `longitude` | decimal | Yes | GPS longitude |
| `photo` | file | No | Selfie photo (jpg, png, max 2MB) |

#### Success Response (201)

```json
{
  "success": true,
  "message": "Clock in berhasil",
  "data": {
    "id": 1,
    "date": "2026-01-20",
    "clock_in_at": "2026-01-20T08:05:00+07:00",
    "clock_out_at": null,
    "is_late": true,
    "late_duration_minutes": 5,
    "late_duration_formatted": "5 menit",
    "location": {
      "id": 1,
      "name": "Kantor Pusat Jakarta",
      "is_within_radius": true,
      "distance_meters": 45
    }
  }
}
```

#### Error Response (422) - Already Clocked In

```json
{
  "success": false,
  "message": "Anda sudah melakukan clock in hari ini",
  "errors": null
}
```

#### Error Response (422) - Outside Location

```json
{
  "success": false,
  "message": "Lokasi Anda di luar area yang diizinkan",
  "errors": {
    "location": ["Jarak Anda 250m dari lokasi terdekat (maks 100m)"]
  }
}
```

---

### 2.3 Clock Out

Record clock out with GPS location and optional photo.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/attendances/clock-out` |
| **Auth Required** | Yes |
| **Content-Type** | `multipart/form-data` |

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `latitude` | decimal | Yes | GPS latitude |
| `longitude` | decimal | Yes | GPS longitude |
| `photo` | file | No | Selfie photo (jpg, png, max 2MB) |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Clock out berhasil",
  "data": {
    "id": 1,
    "date": "2026-01-20",
    "clock_in_at": "2026-01-20T08:05:00+07:00",
    "clock_out_at": "2026-01-20T17:30:00+07:00",
    "is_late": true,
    "late_duration_minutes": 5,
    "is_early_leave": false,
    "work_duration_minutes": 565,
    "work_duration_formatted": "9 jam 25 menit"
  }
}
```

#### Error Response (422) - Not Clocked In

```json
{
  "success": false,
  "message": "Anda belum melakukan clock in hari ini",
  "errors": null
}
```

---

### 2.4 List Attendance History

Get paginated attendance history.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/attendances` |
| **Auth Required** | Yes |

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | int | 1 | Page number |
| `per_page` | int | 15 | Items per page (max 50) |
| `month` | int | current | Filter by month (1-12) |
| `year` | int | current | Filter by year |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "date": "2026-01-20",
      "clock_in_at": "2026-01-20T08:05:00+07:00",
      "clock_out_at": "2026-01-20T17:30:00+07:00",
      "is_late": true,
      "late_duration_minutes": 5,
      "is_early_leave": false,
      "work_duration_minutes": 565,
      "work_duration_formatted": "9 jam 25 menit",
      "source": "mobile"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 45
  }
}
```

---

### 2.5 Get Attendance Summary

Get monthly attendance summary.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/attendances/summary` |
| **Auth Required** | Yes |

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `month` | int | current | Month (1-12) |
| `year` | int | current | Year |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": {
    "month": 1,
    "year": 2026,
    "total_working_days": 22,
    "present": 18,
    "absent": 1,
    "late": 3,
    "on_leave": 2,
    "holidays": 1,
    "total_work_hours": 162,
    "average_clock_in": "08:15",
    "average_clock_out": "17:20"
  }
}
```

---

## 3. Leave

### 3.1 List My Leaves

Get paginated list of my leave requests.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/leaves` |
| **Auth Required** | Yes |

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | int | 1 | Page number |
| `per_page` | int | 15 | Items per page |
| `status` | string | all | Filter: pending, approved, rejected |
| `year` | int | current | Filter by year |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "type": "annual",
      "type_label": "Cuti Tahunan",
      "start_date": "2026-01-25",
      "end_date": "2026-01-26",
      "total_days": 2,
      "reason": "Acara keluarga",
      "status": "pending",
      "status_label": "Menunggu Persetujuan",
      "approved_by": null,
      "created_at": "2026-01-20T10:00:00+07:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 5
  }
}
```

---

### 3.2 Create Leave Request

Submit a new leave request.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/leaves` |
| **Auth Required** | Yes |

#### Request Body

```json
{
  "type": "annual",
  "start_date": "2026-01-25",
  "end_date": "2026-01-26",
  "reason": "Acara keluarga"
}
```

#### Leave Types

| Value | Label |
|-------|-------|
| `annual` | Cuti Tahunan |
| `sick` | Sakit |
| `permission` | Izin |
| `unpaid` | Cuti Tanpa Gaji |

#### Success Response (201)

```json
{
  "success": true,
  "message": "Pengajuan cuti berhasil dikirim",
  "data": {
    "id": 1,
    "type": "annual",
    "type_label": "Cuti Tahunan",
    "start_date": "2026-01-25",
    "end_date": "2026-01-26",
    "total_days": 2,
    "reason": "Acara keluarga",
    "status": "pending",
    "status_label": "Menunggu Persetujuan",
    "created_at": "2026-01-20T10:00:00+07:00"
  }
}
```

#### Error Response (422)

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "start_date": ["Tanggal mulai harus setelah hari ini"],
    "end_date": ["Tanggal selesai harus setelah tanggal mulai"]
  }
}
```

---

### 3.3 Get Leave Detail

Get single leave request detail.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/leaves/{id}` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1,
    "type": "annual",
    "type_label": "Cuti Tahunan",
    "start_date": "2026-01-25",
    "end_date": "2026-01-26",
    "total_days": 2,
    "reason": "Acara keluarga",
    "status": "approved",
    "status_label": "Disetujui",
    "approved_by": {
      "id": "uuid",
      "name": "HR Manager"
    },
    "created_at": "2026-01-20T10:00:00+07:00",
    "updated_at": "2026-01-20T14:00:00+07:00"
  }
}
```

---

### 3.4 Cancel Leave Request

Cancel a pending leave request.

| | |
|---|---|
| **Endpoint** | `DELETE /api/v1/leaves/{id}` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Pengajuan cuti berhasil dibatalkan",
  "data": null
}
```

#### Error Response (422) - Not Pending

```json
{
  "success": false,
  "message": "Hanya pengajuan dengan status pending yang dapat dibatalkan",
  "errors": null
}
```

---

## 4. Overtime

### 4.1 List My Overtimes

Get paginated list of my overtime requests.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/overtimes` |
| **Auth Required** | Yes |

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | int | 1 | Page number |
| `per_page` | int | 15 | Items per page |
| `status` | string | all | Filter: pending, approved, rejected |
| `month` | int | current | Filter by month |
| `year` | int | current | Filter by year |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "date": "2026-01-20",
      "start_time": "18:00",
      "end_time": "21:00",
      "duration_hours": 3,
      "reason": "Deadline project",
      "status": "pending",
      "status_label": "Menunggu Persetujuan",
      "approved_by": null,
      "created_at": "2026-01-20T10:00:00+07:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 3
  }
}
```

---

### 4.2 Create Overtime Request

Submit a new overtime request.

| | |
|---|---|
| **Endpoint** | `POST /api/v1/overtimes` |
| **Auth Required** | Yes |

#### Request Body

```json
{
  "date": "2026-01-20",
  "start_time": "18:00",
  "end_time": "21:00",
  "reason": "Deadline project"
}
```

#### Success Response (201)

```json
{
  "success": true,
  "message": "Pengajuan lembur berhasil dikirim",
  "data": {
    "id": 1,
    "date": "2026-01-20",
    "start_time": "18:00",
    "end_time": "21:00",
    "duration_hours": 3,
    "reason": "Deadline project",
    "status": "pending",
    "status_label": "Menunggu Persetujuan",
    "created_at": "2026-01-20T10:00:00+07:00"
  }
}
```

---

### 4.3 Get Overtime Detail

Get single overtime request detail.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/overtimes/{id}` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": {
    "id": 1,
    "date": "2026-01-20",
    "start_time": "18:00",
    "end_time": "21:00",
    "duration_hours": 3,
    "reason": "Deadline project",
    "status": "approved",
    "status_label": "Disetujui",
    "approved_by": {
      "id": "uuid",
      "name": "HR Manager"
    },
    "created_at": "2026-01-20T10:00:00+07:00",
    "updated_at": "2026-01-20T14:00:00+07:00"
  }
}
```

---

### 4.4 Cancel Overtime Request

Cancel a pending overtime request.

| | |
|---|---|
| **Endpoint** | `DELETE /api/v1/overtimes/{id}` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": "Pengajuan lembur berhasil dibatalkan",
  "data": null
}
```

---

## 5. Supporting Data

### 5.1 Get Attendance Locations

Get list of active attendance locations for geofencing validation.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/locations` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "name": "Kantor Pusat Jakarta",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "radius_meters": 100,
      "address": "Jl. Sudirman No. 1, Jakarta"
    },
    {
      "id": 2,
      "name": "Kantor Cabang Bandung",
      "latitude": -6.9175,
      "longitude": 107.6191,
      "radius_meters": 150,
      "address": "Jl. Asia Afrika No. 10, Bandung"
    }
  ]
}
```

---

### 5.2 Get Holidays

Get list of holidays for calendar display.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/holidays` |
| **Auth Required** | Yes |

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `year` | int | current | Filter by year |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "id": 1,
      "name": "Tahun Baru",
      "date": "2026-01-01",
      "description": "Hari libur nasional"
    },
    {
      "id": 2,
      "name": "Hari Raya Idul Fitri",
      "date": "2026-03-31",
      "description": null
    }
  ]
}
```

---

### 5.3 Get Work Schedules

Get work schedule configuration.

| | |
|---|---|
| **Endpoint** | `GET /api/v1/work-schedules` |
| **Auth Required** | Yes |

#### Success Response (200)

```json
{
  "success": true,
  "message": null,
  "data": [
    {
      "day_of_week": 0,
      "day_name": "Minggu",
      "is_working_day": false,
      "work_start_time": null,
      "work_end_time": null
    },
    {
      "day_of_week": 1,
      "day_name": "Senin",
      "is_working_day": true,
      "work_start_time": "08:00",
      "work_end_time": "17:00"
    },
    {
      "day_of_week": 2,
      "day_name": "Selasa",
      "is_working_day": true,
      "work_start_time": "08:00",
      "work_end_time": "17:00"
    }
  ]
}
```

---

## 6. Error Handling

### Standard Error Response Format

```json
{
  "success": false,
  "message": "Error message here",
  "errors": {
    "field_name": ["Error detail 1", "Error detail 2"]
  }
}
```

### HTTP Status Codes

| Code | Description |
|------|-------------|
| `200` | OK - Request successful |
| `201` | Created - Resource created |
| `400` | Bad Request - Invalid request format |
| `401` | Unauthorized - Invalid/expired token |
| `403` | Forbidden - No permission |
| `404` | Not Found - Resource not found |
| `422` | Unprocessable Entity - Validation failed |
| `500` | Internal Server Error |

### Authentication Errors

#### 401 - Token Invalid

```json
{
  "success": false,
  "message": "Token tidak valid",
  "errors": null
}
```

#### 401 - Token Expired

```json
{
  "success": false,
  "message": "Token sudah kadaluarsa",
  "errors": null
}
```

#### 403 - User Inactive

```json
{
  "success": false,
  "message": "Akun Anda tidak aktif",
  "errors": null
}
```

#### 403 - Not an Employee

```json
{
  "success": false,
  "message": "Akun Anda tidak terdaftar sebagai karyawan",
  "errors": null
}
```

---

## 📝 Implementation Notes

### Response Wrapper

All responses use consistent wrapper:

```php
// Success
return response()->json([
    'success' => true,
    'message' => $message,
    'data' => $data,
], $statusCode);

// Error
return response()->json([
    'success' => false,
    'message' => $message,
    'errors' => $errors,
], $statusCode);
```

### Pagination Meta

Paginated responses include:

```json
{
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

### Date/Time Formats

| Type | Format | Example |
|------|--------|---------|
| Date | `Y-m-d` | `2026-01-20` |
| Time | `H:i` | `08:00` |
| DateTime | ISO 8601 | `2026-01-20T08:00:00+07:00` |

### File Upload

- **Max Size**: 2MB
- **Allowed Types**: jpg, jpeg, png
- **Storage**: Private disk, accessed via signed URL

---

## 🔄 Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-01-20 | Initial API specification |
