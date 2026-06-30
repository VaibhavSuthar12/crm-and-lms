# CRM Backend — Laravel 12

A scalable CRM backend REST API built with Laravel 12, Sanctum (token auth), and Spatie Permissions.

---

## Tech Stack

| Layer        | Technology                              |
|--------------|-----------------------------------------|
| Framework    | Laravel 12                              |
| Auth         | Laravel Sanctum (Bearer Token)          |
| Permissions  | Spatie Laravel Permission               |
| Database     | MySQL 8+ (or PostgreSQL)               |
| PHP          | 8.2+                                    |

---

## Quick Setup

```bash
# 1. Clone & install
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure DB in .env
DB_DATABASE=crm_db
DB_USERNAME=root
DB_PASSWORD=secret

# 4. Migrate & seed
php artisan migrate
php artisan db:seed

# 5. Serve
php artisan serve
```

### Seed Users

| Email              | Password      | Role            |
|--------------------|---------------|-----------------|
| admin@crm.com      | Admin@1234    | Admin           |
| manager@crm.com    | Manager@1234  | Sales Manager   |
| exec@crm.com       | Exec@1234     | Sales Executive |

---

## Roles & Permissions

| Role            | Capabilities                                      |
|-----------------|---------------------------------------------------|
| Admin           | Full access + user management                     |
| Sales Manager   | All leads/customers/tasks + assign leads          |
| Sales Executive | Own leads & tasks only                            |

---

## API Reference

All protected endpoints require: `Authorization: Bearer {token}`

### Authentication

| Method | Endpoint           | Description         |
|--------|--------------------|---------------------|
| POST   | /api/auth/register | Register new user   |
| POST   | /api/auth/login    | Login → get token   |
| GET    | /api/auth/me       | Authenticated user  |
| POST   | /api/auth/refresh  | Refresh token       |
| POST   | /api/auth/logout   | Revoke token        |

### Dashboard

| Method | Endpoint        | Description               |
|--------|-----------------|---------------------------|
| GET    | /api/dashboard  | Stats, charts, activities |

### Leads

| Method | Endpoint                   | Description                  |
|--------|----------------------------|------------------------------|
| GET    | /api/leads                 | List leads (search, paginate)|
| POST   | /api/leads                 | Create lead                  |
| GET    | /api/leads/{id}            | View lead                    |
| PUT    | /api/leads/{id}            | Update lead                  |
| DELETE | /api/leads/{id}            | Soft-delete lead             |
| PATCH  | /api/leads/{id}/assign     | Assign lead to user          |
| PATCH  | /api/leads/{id}/status     | Update lead status           |
| GET    | /api/leads/{id}/timeline   | Activity timeline            |
| POST   | /api/leads/{id}/convert    | Convert lead → customer      |

#### Lead Status Values
`New` · `Contacted` · `Qualified` · `Proposal Sent` · `Won` · `Lost`

#### Query Params for GET /api/leads
| Param       | Example          | Description              |
|-------------|------------------|--------------------------|
| search      | `John`           | Full-text search         |
| status      | `Qualified`      | Filter by status         |
| assigned_to | `3`              | Filter by user ID        |
| per_page    | `25`             | Results per page         |

### Customers

| Method | Endpoint                              | Description              |
|--------|---------------------------------------|--------------------------|
| GET    | /api/customers                        | List customers           |
| GET    | /api/customers/{id}                   | View customer profile    |
| PUT    | /api/customers/{id}                   | Update customer          |
| DELETE | /api/customers/{id}                   | Delete customer          |
| GET    | /api/customers/{id}/timeline          | Activity timeline        |
| POST   | /api/customers/{id}/contacts          | Add contact              |
| DELETE | /api/customers/{id}/contacts/{cId}    | Remove contact           |
| POST   | /api/customers/{id}/notes             | Add note                 |

### Tasks

| Method | Endpoint                     | Description              |
|--------|------------------------------|--------------------------|
| GET    | /api/tasks                   | List tasks               |
| POST   | /api/tasks                   | Create task              |
| GET    | /api/tasks/{id}              | View task                |
| PUT    | /api/tasks/{id}              | Update task              |
| DELETE | /api/tasks/{id}              | Delete task              |
| PATCH  | /api/tasks/{id}/complete     | Mark complete            |

#### Query Params for GET /api/tasks
| Param    | Example      | Description           |
|----------|--------------|-----------------------|
| status   | `Pending`    | Filter by status      |
| priority | `High`       | Filter by priority    |
| overdue  | `true`       | Show overdue tasks    |
| today    | `true`       | Today's follow-ups    |

#### Task Priority: `Low` · `Medium` · `High`
#### Task Status: `Pending` · `In Progress` · `Completed` · `Cancelled`

### Activities

| Method | Endpoint          | Description          |
|--------|-------------------|----------------------|
| GET    | /api/activities   | Global activity feed |

### Users (Admin only)

| Method | Endpoint                          | Description          |
|--------|-----------------------------------|----------------------|
| GET    | /api/users                        | List all users       |
| POST   | /api/users/{id}/toggle-active     | Activate/deactivate  |
| POST   | /api/users/{id}/assign-role       | Change user role     |

---

## Activity Timeline Events

| Type                | When triggered                          |
|---------------------|-----------------------------------------|
| Lead Created        | New lead is created                     |
| Lead Assigned       | Lead is assigned/reassigned             |
| Status Changed      | Lead status is updated                  |
| Customer Converted  | Lead is converted to a customer         |
| Note Added          | Note is added to a customer             |
| Task Created        | New task is created                     |
| Task Completed      | Task is marked as completed             |

---

## Dashboard Response

```json
{
  "stats": {
    "total_leads": 142,
    "total_customers": 38,
    "todays_followups": 5,
    "overdue_tasks": 3,
    "conversion_rate": 26.76
  },
  "leads_by_status": {
    "New": 40,
    "Contacted": 32,
    "Qualified": 28,
    "Proposal Sent": 15,
    "Won": 20,
    "Lost": 7
  },
  "recent_activities": [...]
}
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── LeadController.php
│   │   ├── CustomerController.php
│   │   ├── TaskController.php
│   │   ├── ActivityController.php
│   │   ├── DashboardController.php
│   │   └── UserController.php
│   └── Requests/
│       ├── Auth/
│       ├── Lead/
│       ├── Customer/
│       └── Task/
├── Models/
│   ├── User.php
│   ├── Lead.php
│   ├── Customer.php
│   ├── Contact.php
│   ├── Note.php
│   ├── Task.php
│   └── Activity.php
└── Services/
    └── ActivityService.php

database/
├── migrations/          (7 migration files)
└── seeders/
    └── DatabaseSeeder.php

routes/
└── api.php
```
