# Ogun State Social Registry

## Overview

The **Ogun State Social Registry** is a web-based platform designed to register, manage, and support households and individuals eligible for social intervention programs within Ogun State.

The system enables:

* Household registration using **National Identification Number (NIN)** verification
* Management of household data and members
* Application and tracking of social assistance programs
* Multi-level review and approval workflows
* Field verification by government officers

This platform is built with **Laravel (PHP)** and follows a scalable, secure, and modular architecture suitable for government-grade deployment.

---

## Key Features

### 👤 Household Registration

* NIN verification via third-party services (e.g., Mono/Olarms)
* Automatic population of verified identity details
* Collection of socio-economic data:

  * Household size and composition
  * Income details
  * Housing condition
  * Location (LGA, Ward, Community)

---

### Household Management

* Add and manage household members
* Capture detailed demographic data:

  * Age, gender, relationship
  * Education level
  * Occupation
  * Disability and health status

---

### Social Assistance Applications

Households can apply for:

* Cash Transfer
* Food Support
* Healthcare Subsidy
* School Support Grant

Each application includes:

* Reason for request
* Beneficiary count
* Unique application reference number

---

### 🔄 Workflow & Approval System

* Multi-stage application lifecycle:

  * Submitted
  * Under Review
  * Field Verification
  * Approved / Rejected

* Role-based access:

  * Super Admin
  * Reviewing Officers
  * Field Officers

---

### Field Verification

* Officers can be assigned to visit households
* Capture findings and verification status
* Ensure authenticity of applications

---

### Dashboard & Tracking

* Applicants can:

  * View their profile
  * Track application status
  * Manage household members

* Admins can:

  * Monitor applications
  * Assign officers
  * Review and approve submissions

---

## System Architecture

### Backend

* **Framework:** Laravel
* **Authentication:** Laravel Sanctum (Token-based API authentication)
* **Database:** MySQL

### Core Modules

* Authentication & Authorization
* Household Registration
* Assistance Application Management
* Role & Permission Management
* Field Operations (Verification Visits)
* Activity Logging & Audit Trail

---

## Database Highlights

The system is structured around the following core entities:

* **Users** (household & staff)
* **Households**
* **Household Members**
* **Assistance Applications**
* **Application Stages (workflow tracking)**
* **Field Visits**
* **Roles & Permissions**
* **Lookup Tables** (LGAs, Wards, Occupations, etc.)

---

## Authentication

Authentication is handled using **Laravel Sanctum**:

* Token-based authentication for API access
* Protected routes using `auth:sanctum` middleware
* JSON-based error handling (no redirects)

---

## Installation Guide

### 1. Clone Repository

```bash
git clone https://github.com/your-org/ogun-social-registry.git
cd ogun-social-registry
```

---

### 2. Install Dependencies

```bash
composer install
```

---

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials.

---

### 4. Run Migrations

```bash
php artisan migrate
```

---

### 5. Seed Database (Optional but recommended)

```bash
php artisan db:seed
```

Seeds include:

* LGAs, Wards, Communities
* Assistance Types
* Occupations, Disabilities, etc.

---

### 6. Run Application

```bash
php artisan serve
```

---

## 🔗 API Structure (Sample)

| Method | Endpoint                    | Description              |
| ------ | --------------------------- | ------------------------ |
| POST   | `/api/login`                | Authenticate user        |
| POST   | `/api/register-household`   | Register household       |
| POST   | `/api/add-household-member` | Add member               |
| POST   | `/api/apply-assistance`     | Apply for support        |
| GET    | `/api/applications`         | List applications        |
| POST   | `/api/create_admin`         | Create admin (protected) |

---

## Business Logic Highlights

* **NIN Verification:** Required before household registration
* **Reference Generation:**

  * Household → `OG-HH-YYYY-XXXXX`
  * Application → `OG-APP-YYYY-XXXXX`
* **Multi-role Users:** Users can have multiple roles
* **Stage Tracking:** Each application maintains a full audit trail

---

## Security Considerations

* Token-based authentication (Sanctum)
* Role-based authorization
* Input validation on all endpoints
* Activity logging for audit purposes
* No sensitive data exposed via API

---

## Future Enhancements

* Mobile app integration
* GIS mapping for household locations
* SMS/Email notification system
* Advanced analytics dashboard
* Offline support for field officers

---

## Contributors

* Backend Team – Brookes Professional Services Limited
* Government Stakeholders & Field Officers

---

## License

This project is proprietary and developed for the **Ogun State Government**. Unauthorized distribution or use is prohibited.

---

## Support

For technical support or inquiries, contact the development team or system administrator.

---

**Ogun State Social Registry — Enabling data-driven social intervention.**
