# 🚀 PeopleSync Mobile App - Flutter Development Prompt

> **For use with**: Google Antigravity (Gemini 3 Pro / Claude Opus 4.5)
> **Project**: people-sync-mobile
> **Type**: Employee Attendance Mobile App (MVP)

---

## 📋 PROJECT CONTEXT

I'm building a Flutter mobile app called **PeopleSync Mobile** for employee attendance. The backend API is already complete and tested (Laravel 12 + JWT). I'm a Flutter newbie and need your help to build this properly with best practices.

### What the app does:
1. **Employee Login** - JWT authentication
2. **Clock In/Out** - With GPS location (geofencing)
3. **Attendance History** - View personal attendance records
4. **Leave Request** - Submit and view leave requests
5. **Overtime Request** - Submit and view overtime requests

### Backend API Information:
- **Base URL**: Will be configured via environment
- **Auth**: JWT Bearer Token
- **Token Expiry**: 1 hour (3600 seconds)
- **API Version**: v1 (`/api/v1/*`)

---

## 🎯 WHAT I NEED YOU TO DO

### Phase 1: Project Setup & Architecture (Do First)

1. **Set up proper Flutter project structure** following clean architecture:
   ```
   lib/
   ├── core/                    # Core utilities, constants, themes
   │   ├── constants/           # App constants, API endpoints
   │   ├── errors/              # Custom exceptions & failures
   │   ├── network/             # HTTP client, interceptors, API service
   │   ├── storage/             # Secure storage for tokens
   │   ├── theme/               # App theme, colors, text styles
   │   └── utils/               # Helpers, extensions, validators
   ├── features/                # Feature modules
   │   ├── auth/                # Login, logout, session
   │   ├── home/                # Dashboard/home screen
   │   ├── attendance/          # Clock in/out, history, summary
   │   ├── leave/               # Leave requests
   │   └── overtime/            # Overtime requests
   ├── shared/                  # Shared widgets, models
   │   ├── widgets/             # Reusable UI components
   │   └── models/              # Shared data models
   └── main.dart
   ```

2. **Install and configure these packages**:
   - `dio` - HTTP client with interceptors
   - `flutter_secure_storage` - Secure token storage
   - `flutter_riverpod` or `bloc` - State management (recommend Riverpod for simplicity)
   - `go_router` - Navigation
   - `geolocator` - GPS location
   - `intl` - Date/time formatting
   - `flutter_dotenv` - Environment variables
   - `connectivity_plus` - Network status
   - `freezed` + `json_serializable` - Data classes

3. **Create secure API layer with these features**:
   - JWT token management (store, refresh, clear)
   - Automatic token refresh before expiry
   - Request/response interceptors for logging
   - Error handling with user-friendly messages
   - Rate limiting protection (client-side throttling)
   - Network connectivity check
   - Request timeout handling
   - Retry mechanism for failed requests

---

### Phase 2: Core Network Layer Implementation

Create a robust API service layer. Here's what I need:

#### 2.1 API Configuration
```dart
// Example structure needed
class ApiConfig {
  static const String baseUrl = 'BASE_URL_FROM_ENV';
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
  static const int maxRetries = 3;
  static const Duration rateLimitWindow = Duration(seconds: 1);
  static const int maxRequestsPerWindow = 10;
}
```

#### 2.2 Token Manager
- Store JWT securely using flutter_secure_storage
- Auto-refresh token when close to expiry (e.g., 5 minutes before)
- Clear token on logout
- Check token validity before requests

#### 2.3 Dio Interceptors
Create these interceptors:
1. **AuthInterceptor** - Add Bearer token to requests
2. **LoggingInterceptor** - Log requests/responses (debug only)
3. **ErrorInterceptor** - Transform API errors to app exceptions
4. **RetryInterceptor** - Retry failed requests
5. **RateLimitInterceptor** - Client-side rate limiting

#### 2.4 API Response Wrapper
Handle the API response format:
```json
{
  "success": true/false,
  "message": "string or null",
  "data": {...},
  "errors": {...} // validation errors
}
```

#### 2.5 Custom Exceptions
Create exceptions for:
- NetworkException (no internet)
- ServerException (5xx errors)
- UnauthorizedException (401 - token expired)
- ForbiddenException (403 - not employee)
- ValidationException (422 - form errors)
- TimeoutException
- RateLimitException

---

### Phase 3: API Endpoints to Integrate

Here are the endpoints from my backend. Create repository classes for each:

#### Authentication (`/api/v1/auth/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/login` | Login with email & password |
| GET | `/auth/me` | Get current user + employee |
| POST | `/auth/refresh` | Refresh JWT token |
| POST | `/auth/logout` | Logout |

**Login Request:**
```json
{ "email": "string", "password": "string" }
```

**Login Response:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": { "id": "uuid", "name": "string", "email": "string" },
    "employee": {
      "id": 1,
      "employee_number": "EMP001",
      "name": "string",
      "department": { "id": 1, "name": "string" },
      "status": "active",
      "joined_at": "2023-01-15"
    }
  }
}
```

#### Attendance (`/api/v1/attendances/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/attendances` | List history (paginated) |
| GET | `/attendances/today` | Today's attendance status |
| GET | `/attendances/summary` | Monthly summary |
| POST | `/attendances/clock-in` | Clock in with GPS |
| POST | `/attendances/clock-out` | Clock out with GPS |

**Clock In/Out Request:**
```json
{
  "latitude": -7.47110419,
  "longitude": 112.72356475
}
```

**Today's Attendance Response (no attendance yet):**
```json
{
  "success": true,
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

**Today's Attendance Response (already clocked in):**
```json
{
  "success": true,
  "data": {
    "id": 36,
    "date": "2026-01-20",
    "clock_in_at": "2026-01-20T08:05:00+07:00",
    "clock_out_at": null,
    "is_late": true,
    "late_duration_minutes": 5,
    "late_duration_formatted": "5 menit",
    "can_clock_in": false,
    "can_clock_out": true
  }
}
```

#### Leave (`/api/v1/leaves/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/leaves` | List my leave requests |
| POST | `/leaves` | Submit leave request |
| GET | `/leaves/{id}` | Get leave detail |
| DELETE | `/leaves/{id}` | Cancel pending leave |

**Create Leave Request:**
```json
{
  "type": "annual|sick|permission|unpaid",
  "start_date": "2026-02-01",
  "end_date": "2026-02-02",
  "reason": "optional string"
}
```

#### Overtime (`/api/v1/overtimes/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/overtimes` | List my overtime requests |
| POST | `/overtimes` | Submit overtime request |
| GET | `/overtimes/{id}` | Get overtime detail |
| DELETE | `/overtimes/{id}` | Cancel pending overtime |

**Create Overtime Request:**
```json
{
  "date": "2026-01-25",
  "start_time": "17:00",
  "end_time": "20:00",
  "reason": "optional string"
}
```

#### Supporting Data
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/locations` | Office locations for geofencing |
| GET | `/holidays` | Holidays list |
| GET | `/work-schedules` | Work schedule config |

---

### Phase 4: UI/UX Design (MVP)

I need a simple, clean, professional UI. Here's my preference:

#### Design System:
- **Primary Color**: Blue (#2563EB) or similar professional color
- **Style**: Material Design 3
- **Font**: Default (Roboto) is fine
- **Theme**: Support light mode only for MVP

#### Screens Needed:

1. **Splash Screen**
   - App logo
   - Check auth status → redirect to Login or Home

2. **Login Screen**
   - Email input
   - Password input (with show/hide toggle)
   - Login button
   - Loading state
   - Error messages

3. **Home Screen (Dashboard)**
   - Greeting with employee name
   - Today's date
   - Attendance status card:
     - "Belum Clock In" / "Sudah Clock In (08:05)" / "Sudah Clock Out"
     - Clock In/Out button (big, prominent)
     - Show if late
   - Quick stats: Present this month, Late count
   - Bottom navigation: Home, History, Leave, Profile

4. **Clock In/Out Flow**
   - Request location permission
   - Show current GPS coordinates
   - Show nearest office location & distance
   - If within radius: Allow clock in/out
   - If outside radius: Show error with distance
   - Loading & success/error feedback

5. **Attendance History Screen**
   - Month/year picker
   - List of attendance records
   - Each item shows: Date, Clock in time, Clock out time, Status (Late/On time)
   - Color coding: Green (on time), Red (late), Gray (absent)

6. **Leave Request Screen**
   - Tab: My Requests / New Request
   - List of requests with status badge (Pending/Approved/Rejected)
   - Form: Type dropdown, Date range picker, Reason text

7. **Overtime Request Screen**
   - Similar to Leave Request
   - Form: Date picker, Start time, End time, Reason

8. **Profile Screen**
   - Employee info (name, number, department)
   - Work schedule info
   - Logout button

#### Navigation:
- Bottom Navigation Bar with 4 tabs: Home, History, Leave, Profile
- Overtime accessible from Profile or Home

---

### Phase 5: Implementation Order

Please implement in this order:

1. **Project setup** - Folder structure, dependencies
2. **Core layer** - API service, interceptors, storage, exceptions
3. **Auth feature** - Login screen, token management, auto-login
4. **Home feature** - Dashboard with attendance status
5. **Attendance feature** - Clock in/out with GPS
6. **History feature** - Attendance history list
7. **Leave feature** - Leave request CRUD
8. **Overtime feature** - Overtime request CRUD
9. **Profile feature** - Profile screen with logout

---

## 🔒 SECURITY REQUIREMENTS

1. **Never hardcode API URL or secrets** - Use .env file
2. **Store JWT in secure storage** - Not SharedPreferences
3. **Clear sensitive data on logout** - Token, user data
4. **Validate input before sending** - Email format, required fields
5. **Handle token expiry gracefully** - Auto-refresh or redirect to login
6. **Don't log sensitive data** - Hide tokens in logs
7. **Implement certificate pinning** (optional for MVP)

---

## 📱 DEVICE PERMISSIONS

The app needs these permissions:

**Android (android/app/src/main/AndroidManifest.xml):**
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
```

**iOS (ios/Runner/Info.plist):**
```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>We need your location to verify attendance at office</string>
<key>NSLocationAlwaysUsageDescription</key>
<string>We need your location to verify attendance at office</string>
```

---

## 🧪 TESTING INFORMATION

**Test Credentials:**
- Email: `budi.santoso@company.com`
- Password: `12345678`

**Test Location (Office):**
- Name: MCG B5-20
- Latitude: -7.47110419
- Longitude: 112.72356475
- Radius: 20 meters

**API Base URL for Development:**
- Will be provided via environment variable
- Example: `http://192.168.x.x:8000/api/v1` (local network)

---

## 📝 CODE QUALITY REQUIREMENTS

1. **Follow Dart/Flutter conventions** - effective_dart
2. **Use meaningful variable/function names** in English
3. **Add comments for complex logic**
4. **Handle all error states** - loading, error, empty, success
5. **Make widgets small and reusable**
6. **Use const constructors where possible**
7. **Format code with `dart format`**
8. **No hardcoded strings** - Use constants or localization

---

## 🚨 ERROR MESSAGES (Indonesian)

Use these messages for user-facing errors:

| Error Type | Message |
|------------|---------|
| No Internet | "Tidak ada koneksi internet" |
| Server Error | "Terjadi kesalahan pada server" |
| Session Expired | "Sesi Anda telah berakhir, silakan login kembali" |
| Invalid Credentials | "Email atau password salah" |
| Not Employee | "Akun Anda tidak terdaftar sebagai karyawan" |
| Validation Error | Show field-specific error |
| Location Error | "Tidak dapat mengakses lokasi" |
| Outside Geofence | "Anda berada di luar area kantor" |
| Already Clocked In | "Anda sudah melakukan clock in hari ini" |
| Not Clocked In | "Anda belum melakukan clock in hari ini" |

---

## ✅ DELIVERABLES CHECKLIST

After implementation, I should have:

- [ ] Clean project structure following architecture
- [ ] Secure API layer with interceptors
- [ ] JWT token management with auto-refresh
- [ ] Login screen with validation
- [ ] Home dashboard with attendance status
- [ ] Clock in/out with GPS validation
- [ ] Attendance history with monthly view
- [ ] Leave request (list + create + cancel)
- [ ] Overtime request (list + create + cancel)
- [ ] Profile screen with logout
- [ ] Proper error handling throughout
- [ ] Loading states for all async operations
- [ ] Environment configuration (.env)

---

## 🎬 START HERE

Please start by:

1. **Review this entire prompt** to understand the full scope
2. **Set up the project structure** as described in Phase 1
3. **Create the core network layer** with all security features
4. **Show me the folder structure** before proceeding with features
5. **Implement one feature at a time**, showing me the code and explaining what it does

I'll work with you step by step. If something is unclear, please ask before implementing.

**Mode: Plan first, then Act when I approve.**

Let's build a production-quality MVP together! 🚀
