**PeopleSync — Employee Attendance System (MVP)**

PeopleSync is a mobile & web-based employee attendance application designed as an HRIS MVP with a focus on data accuracy, auditability, and backend scalability.

This project was created as a backend / fullstack engineer portfolio, with a real-world system design approach, not just CRUD.

## Key Features (MVP)
### Employee (Mobile App)

* Clock-in / clock-out
* Attendance history
* Leave request
* Overtime request

### HR / Admin (Web App)

* Employee management
* Department hierarchy
* Manual attendance assistance
* Attendance correction (with audit log)
* Leave & overtime approval
* Attendance audit logs

### Architecture Overview
Flutter (Mobile / Web)
        ↓
Laravel API (JWT, RBAC)
        ↓
PostgreSQL

### Key Architectural Decisions

* Stateless JWT authentication
* Server-side RBAC (no role embedded in token)
* Separation of state vs event (attendance & logs)
* Soft delete semantics with partial unique indexes
* Strong database constraints over application trust

### Authentication & Authorization

* JWT used as identity token only
* Role & permission resolved server-side
* Soft-deleted users are treated as inactive
* Designed to support real-time role changes

## Attendance Design (Core Highlight)
### Attendance (State)

* One record per employee per day
* Used for reporting & payroll

### Attendance Logs (Event)

* Records all actions:
    * clock-in
    * clock-out
    * manual assistance
    * corrections

* Immutable & audit-ready

This design allows:
* HR intervention without data corruption
* Strong audit trail
* Clear dispute resolution

## Database Design Principles

* PostgreSQL with:
    * Partial unique indexes
    * Explicit FK behaviors

* UUID for user identity
* BIGINT for internal relations
* Soft delete used intentionally (not everywhere)

## Tech Stack
Layer   --------  Technology
Backend	--------  Laravel
Database -------  PostgreSQL
Auth    --------  JWT
Admin UI -------  Filament
Mobile  --------  Flutter
Cache (future)	  Redis

## Roadmap
### MVP (Current)
* Authentication & RBAC
* Attendance system
* Leave & overtime
* Audit logs
* Admin assistance flow

### Post-MVP
* Payroll
* Shift scheduling
* Analytics dashboard
* Multi-tenant support

## Why This Project?

This project demonstrates:
* Real-world backend architecture decisions
* Strong data modeling
* Security-aware authentication
* Business-driven system design

## Final Note
* PeopleSync is intentionally designed as a production-minded MVP, focusing on correctness, auditability, and extensibility rather than feature count.