# Digital Inspection & Reporting System

A Laravel-based web application for managing digital inspections, field data capture, questionnaire configuration, and multi-role reporting across clients, sites, and buildings.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Roles & Permissions](#roles--permissions)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Seeding](#seeding)
- [Development](#development)
- [Security](#security)

---

## Overview

The Digital Inspection & Reporting System provides a structured workflow for:

- Configuring inspection templates (questionnaires, sections, field types)
- Capturing inspections, re-inspections, and installation/rectification jobs in the field
- Reviewing and approving inspection reports by managers and reviewers
- Giving clients read-only portal access to relevant assets and documents
- Maintaining a full audit log of all user activity

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Auth & UI scaffolding | Laravel Breeze |
| RBAC | Spatie Laravel Permission v8 |
| Audit Logging | Spatie Laravel ActivityLog v4 |
| Frontend build | Vite 8, Alpine.js 3 |
| CSS | Bootstrap 5 (Velzon admin template), Tailwind CSS 3 |
| Database | SQLite (default), MySQL/PostgreSQL compatible |
| Queue / Cache | Database driver (default) |
| Testing | PHPUnit 12 |

---

## Features

### Master Data Management
- **Clients** — organisation records linked to sites and users
- **Sites** — physical locations belonging to clients
- **Buildings** — structures within a site
- **Sections** — logical groupings for questionnaire items
- **Field Types** — data-type definitions for questionnaire fields
- **Data Types** — enum-backed type catalogue
- **Master Lookups** — reusable lookup values for dropdowns

### Questionnaire Builder
- Hierarchical question structure (parent / sub-questionnaire)
- Assign field type, section, enabled/required flags, and status per item
- UUIDs on all questionnaire and section records
- Soft-delete support

### Inspection Workflow
- Jobs management with technician assignment
- Inspection capture (field technicians)
- Re-inspection capture
- Installation & rectification capture
- Review / approval flows (managers, reviewers)

### Document Management
- Upload, manage, approve, send, and download documents
- Separate export permissions for internal and client-facing records

### User & Role Management
- CRUD for users with role assignment
- Permissions grouped by module (see [Roles & Permissions](#roles--permissions))
- Soft-delete on users with `created_by` / `updated_by` tracking

### Client Portal
- Scoped read-only access for external client users
- Download and export of client-relevant documents and assets

### Audit Log
- Full activity log of authentication events and model changes
- Viewable by system administrators

---

## Roles & Permissions

| Role | Key Permissions |
|---|---|
| `system-administrator` | Full access to all modules including system configuration and audit log |
| `manager` | Jobs, inspections, assets, documents, user management, export; no system config or audit log |
| `field-technician` | View jobs; capture inspections, re-inspections, installations; view assets and documents |
| `client-user` | Client portal access; view/download documents and assets; export client records |

Full permission list is defined in [database/seeders/RbacSeeder.php](database/seeders/RbacSeeder.php).

Permissions are grouped into these modules:

- Jobs
- Inspections
- Re-Inspections
- Installation & Rectification
- Asset Register
- Documents
- Client Portal
- User & Role Management
- Audit Log
- Export

---

## Project Structure

```
app/
  Enums/
    DataType.php              # DataType enum
  Http/
    Controllers/
      Admin/
        AuditLogController.php
        PermissionController.php
        RbacController.php
        Master/
          BuildingController.php
          ClientController.php
          DataTypeController.php
          MasterLookupController.php
          QuestionnaireController.php
          SectionController.php
          SiteController.php
      Auth/                   # Breeze auth controllers
      ProfileController.php
    Middleware/
      SecurityHeaders.php     # Security response headers
    Requests/
      Auth/
      ProfileUpdateRequest.php
  Listeners/
    LogAuthActivity.php       # Auth event → activity log
  Models/
    Building.php
    Client.php
    FieldType.php
    MasterLookup.php
    Questionnaire.php         # UUID, SoftDeletes, hierarchical
    Section.php               # UUID, SoftDeletes
    Site.php
    User.php                  # SoftDeletes, created_by/updated_by
  Providers/
    AppServiceProvider.php
  View/Components/
    AppLayout.php
    GuestLayout.php
  helpers.php

database/
  migrations/                 # Chronological schema history
  seeders/
    DatabaseSeeder.php
    RbacSeeder.php            # Roles, permissions, default admin user

resources/views/
  admin/                      # System administrator views
    audit-log/
    dashboard.blade.php
    master/                   # CRUD views for all master data
    permissions/
    rbac/
    users/
  auth/                       # Breeze auth pages
  client/dashboard.blade.php
  manager/dashboard.blade.php
  reviewer/dashboard.blade.php
  technician/dashboard.blade.php
  layouts/
    app.blade.php
    sidebar.blade.php
    guest.blade.php
  profile/
  components/

public/assets/                # Velzon admin template static assets
  css/                        # Bootstrap 5, icons, custom styles
  fonts/                      # Boxicons, Line Awesome
```

---

## Database Schema

### Core models and their key columns

| Table | Key Columns |
|---|---|
| `users` | `id`, `name`, `email`, `password`, `client_id`, `created_by`, `updated_by`, `deleted_at` |
| `clients` | `id`, `name`, ... |
| `sites` | `id`, `client_id`, `name`, ... |
| `buildings` | `id`, `site_id`, `name`, ... |
| `sections` | `id` (UUID), `name`, `key`, `description`, `status`, `deleted_at` |
| `questionnaires` | `id` (UUID), `name`, `key`, `type`, `field_type_id`, `section_id`, `parent_id`, `enabled`, `required`, `status`, `deleted_at` |
| `field_types` | `id`, `name`, ... |
| `master_lookups` | `id`, `name`, ... |
| `user_site` | Pivot — `user_id`, `site_id` |
| `permissions` | `id`, `name`, `guard_name`, `module` |
| `roles` | `id`, `name`, `guard_name` |
| `activity_log` | Full spatie activitylog schema with `event` and `batch_uuid` columns |

---

## Installation

### Prerequisites

- PHP >= 8.3
- Composer
- Node.js >= 18 + npm
- SQLite (default) or MySQL/PostgreSQL

### Quick setup (all-in-one)

```bash
composer run setup
```

This runs: `composer install` → copy `.env` → generate app key → migrate → `npm install` → `npm run build`.

### Manual setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Install Node dependencies and build assets
npm install
npm run build
```

---

## Environment Configuration

Copy `.env.example` to `.env` and update the values below for your environment.

```dotenv
APP_NAME=DigitalInspection
APP_ENV=local           # set to 'production' for live
APP_KEY=                # generated by artisan key:generate
APP_DEBUG=true          # set to false in production
APP_URL=http://localhost

# Database — defaults to SQLite
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=digital_inspection
# DB_USERNAME=root
# DB_PASSWORD=

# Mail — defaults to log driver (no actual email sent)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

For MySQL, uncomment the `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` lines and set `DB_CONNECTION=mysql`.

---

## Seeding

Run the RBAC seeder to create roles, permissions, and the default system administrator account:

```bash
php artisan db:seed
```

### Default admin credentials

| Field | Value |
|---|---|
| Email | `admin@example.com` |
| Password | `Admin@12345` |

**Change this password immediately after first login in any non-local environment.**

---

## Development

Start all services (Laravel server, queue worker, log tail, and Vite HMR) concurrently:

```bash
composer run dev
```

Or run them individually:

```bash
php artisan serve          # Web server at http://localhost:8000
npm run dev                # Vite HMR
php artisan queue:listen   # Queue worker
php artisan pail           # Log viewer
```

### Running tests

```bash
composer run test
# or
php artisan test
```

---

## Security

The application applies the following HTTP security headers via `SecurityHeaders` middleware on every response:

| Header | Value |
|---|---|
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |

Additional security measures:
- RBAC enforced via Spatie Laravel Permission on all admin and role-sensitive routes
- Soft-deletes on users (no hard data removal)
- Full audit trail via Spatie ActivityLog
- BCRYPT password hashing with 12 rounds
- Session encryption disabled by default (enable via `SESSION_ENCRYPT=true` in production)
