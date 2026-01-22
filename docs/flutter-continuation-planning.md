# 🔄 PeopleSync Mobile - Continuation Prompt

> **Project**: `/mnt/d/flutter-projects/flutter_absensi`
> **GitHub**: https://github.com/DwikyAnggarda/people-sync-mobile
> **Status**: Initial setup complete, need to fix warnings and continue features

---

## 📍 Current Progress

The Flutter project has been set up with:

✅ **Completed:**
- Project structure (clean architecture)
- Core layer (API client, Dio interceptors, secure storage, theme)
- Auth feature (login screen, splash screen, auth provider)
- Attendance feature (models, repository, provider, location service)
- Home screen with attendance card and clock modal

⚠️ **Has Warnings (need fix):**
- `lib/features/home/screens/home_screen.dart` - 4 warnings
- `lib/features/home/widgets/attendance_card.dart` - 3 warnings

❌ **Not Yet Built:**
- Attendance history screen
- Leave feature (list, create, cancel)
- Overtime feature (list, create, cancel)
- Profile screen with logout
- Bottom navigation integration

---

## 🎯 TASK 1: Fix Warnings

Please review and fix the warnings in these files:

1. `lib/features/home/screens/home_screen.dart`
2. `lib/features/home/widgets/attendance_card.dart`

Run `flutter analyze` to see the warnings, then fix them.

---

## 🎯 TASK 2: Complete Attendance History

Create the attendance history screen with:

**File:** `lib/features/attendance/screens/attendance_history_screen.dart`

**Features:**
- Month/year picker at the top
- List of attendance records
- Each item shows: Date, Clock in time, Clock out time, Status badge
- Color coding: Green (on time), Orange (late), Gray (no record)
- Pagination support
- Pull to refresh

**API Endpoint:**
```
GET /api/v1/attendances?month=1&year=2026&page=1&per_page=15
```

---

## 🎯 TASK 3: Build Leave Feature

Create leave request feature:

**Files to create:**
- `lib/features/leave/data/models/leave_model.dart`
- `lib/features/leave/data/repositories/leave_repository.dart`
- `lib/features/leave/providers/leave_provider.dart`
- `lib/features/leave/screens/leave_screen.dart`
- `lib/features/leave/widgets/leave_form.dart`
- `lib/features/leave/widgets/leave_list_item.dart`

**Features:**
- Tab view: "Riwayat" (history) and "Ajukan Cuti" (new request)
- List of leave requests with status badge (Pending/Approved/Rejected)
- Form with: Type dropdown, Date range picker, Reason text field
- Cancel pending leave requests

**Leave Types:**
| Value | Label |
|-------|-------|
| `annual` | Cuti Tahunan |
| `sick` | Sakit |
| `permission` | Izin |
| `unpaid` | Cuti Tanpa Gaji |

**API Endpoints:**
```
GET /api/v1/leaves
POST /api/v1/leaves
DELETE /api/v1/leaves/{id}
```

---

## 🎯 TASK 4: Build Overtime Feature

Create overtime request feature (similar structure to Leave):

**Files to create:**
- `lib/features/overtime/data/models/overtime_model.dart`
- `lib/features/overtime/data/repositories/overtime_repository.dart`
- `lib/features/overtime/providers/overtime_provider.dart`
- `lib/features/overtime/screens/overtime_screen.dart`
- `lib/features/overtime/widgets/overtime_form.dart`
- `lib/features/overtime/widgets/overtime_list_item.dart`

**Features:**
- Tab view: "Riwayat" and "Ajukan Lembur"
- List of overtime requests with status badge
- Form with: Date picker, Start time, End time, Reason
- Cancel pending overtime requests

**API Endpoints:**
```
GET /api/v1/overtimes
POST /api/v1/overtimes
DELETE /api/v1/overtimes/{id}
```

---

## 🎯 TASK 5: Build Profile Screen

Create profile screen:

**File:** `lib/features/profile/screens/profile_screen.dart`

**Features:**
- Employee info card (name, employee number, department)
- Work schedule info
- App version
- Logout button with confirmation dialog

---

## 🎯 TASK 6: Bottom Navigation

Update the main scaffold to have proper bottom navigation:

**Tabs:**
1. **Beranda** (Home) - Home icon → HomeScreen
2. **Riwayat** (History) - History icon → AttendanceHistoryScreen  
3. **Cuti** (Leave) - Calendar icon → LeaveScreen
4. **Profil** (Profile) - Person icon → ProfileScreen

**Note:** Overtime is accessible from Profile screen via a menu item.

---

## 📋 API Response Format Reference

All API responses follow this format:

```json
{
  "success": true,
  "message": "string or null",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

**Error format:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error detail"]
  }
}
```

---

## 🔧 Implementation Order

Please implement in this order:
1. Fix warnings first
2. Attendance history screen
3. Leave feature (complete)
4. Overtime feature (complete)
5. Profile screen
6. Bottom navigation integration
7. Test the app

After each task, commit the changes with a descriptive message.

---

## ✅ When Done

After completing all tasks:
1. Run `flutter analyze` - should show no issues
2. Run `flutter build apk --debug` - should build successfully
3. Commit and push to GitHub

Let's start with **Task 1: Fix the warnings**. Run `flutter analyze` and show me the issues.
