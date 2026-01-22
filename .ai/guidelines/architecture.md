# Project Architecture

## Overview

Job Board - A Laravel REST API backend for a React job listing platform. Candidates can browse, apply for, and save jobs. Employers can post and manage job listings.

## Tech Stack

- **Framework**: Laravel 12
- **Database**: PostgreSQL (Docker)
- **Server**: PHP 8.4 (Docker Sail)
- **Frontend**: React (separate repository)
- **API**: REST (versioned at /api/v1)

## Directory Structure

```
job-board/
├── app/
│   ├── Models/           # Eloquent models
│   │   ├── User.php
│   │   ├── Employer.php
│   │   ├── Candidate.php
│   │   ├── JobListing.php
│   │   ├── Application.php
│   │   ├── SavedJob.php (implicit through belongsToMany)
│   │   └── Category.php
│   ├── Http/
│   │   ├── Controllers/  # API endpoint handlers
│   │   │   ├── AuthController.php
│   │   │   ├── EmployerController.php
│   │   │   ├── CandidateController.php
│   │   │   ├── JobListingController.php
│   │   │   ├── ApplicationController.php
│   │   │   └── CategoryController.php
│   │   ├── Requests/    # Form request validation classes
│   │   └── Resources/   # API response transformation (future)
│   ├── Services/        # Business logic (optional, future)
│   └── Providers/       # Service providers
├── database/
│   ├── migrations/      # All table schemas (ordered by timestamp)
│   ├── factories/       # Model factories for testing
│   └── seeders/         # Database seeders
├── routes/
│   └── api.php         # API routes (versioned)
├── tests/
│   ├── Feature/        # API endpoint tests
│   └── Unit/           # Unit tests
├── .ai/
│   └── guidelines/     # This documentation
│       ├── api-conventions.md
│       └── architecture.md
├── compose.yaml        # Docker Sail configuration
├── .env                # Environment variables (gitignored)
└── .env.example        # Template (commit to repo)
```

## Database Schema

### Users (Auth Base)

- `id` (PK)
- `first_name`, `last_name`
- `email` (unique)
- `password` (hashed)
- `email_verified_at` (nullable)
- `timestamps`

**Role-based access:**

- Has one `Employer` → admin/employer user
- Has one `Candidate` → admin/candidate user

### Employers (1:1 with Users)

- `id` (PK)
- `user_id` (FK → users)
- `company_name`
- `company_logo` (file path or nullable)
- `company_website`
- `company_description`
- `location`
- `timestamps`

### Candidates (1:1 with Users)

- `id` (PK)
- `user_id` (FK → users)
- `phone`
- `resume` (file path or nullable)
- `bio` (text)
- `skills` (JSON or text - store comma-separated or JSON)
- `timestamps`

### JobListings (1:N with Employers)

- `id` (PK)
- `employer_id` (FK → employers)
- `title`
- `description` (text)
- `requirements` (nullable)
- `location`
- `employment_type` (enum: full-time, part-time, contract)
- `salary_min`, `salary_max` (decimal, nullable)
- `status` (enum: open, closed)
- `expires_at` (nullable)
- `timestamps`

### Applications (Junction: Candidates ↔ JobListings)

- `id` (PK)
- `candidate_id` (FK → candidates)
- `job_listing_id` (FK → job_listings)
- `cover_letter` (nullable)
- `status` (enum: pending, reviewed, accepted, rejected)
- `applied_at` (timestamp)
- `reviewed_at` (nullable)
- `timestamps`
- **Unique constraint**: `(candidate_id, job_listing_id)` - one application per candidate per job

### SavedJobs (Junction: Candidates ↔ JobListings)

- `id` (PK)
- `candidate_id` (FK → candidates)
- `job_listing_id` (FK → job_listings)
- `timestamps`
- **Unique constraint**: `(candidate_id, job_listing_id)` - prevent duplicate saves

### Categories

- `id` (PK)
- `name`
- `slug` (unique, URL-friendly)
- `description` (nullable)
- `timestamps`

### CategoryJobListing (Junction: Categories ↔ JobListings)

- `id` (PK)
- `category_id` (FK → categories)
- `job_listing_id` (FK → job_listings)
- `timestamps`

## Model Relationships

### User

```php
hasOne(Employer)  // if employer role
hasOne(Candidate) // if candidate role
```

### Employer

```php
belongsTo(User)
hasMany(JobListing)
```

### Candidate

```php
belongsTo(User)
hasMany(Application)
belongsToMany(JobListing, 'saved_jobs') // SavedJobs pivot
```

### JobListing

```php
belongsTo(Employer)
hasMany(Application)
belongsToMany(Candidate, 'saved_jobs')  // Who saved this job
belongsToMany(Category, 'category_job_listing')
```

### Application

```php
belongsTo(Candidate)
belongsTo(JobListing)
```

### Category

```php
belongsToMany(JobListing, 'category_job_listing')
```

## Model Configuration

### Fillable Properties

- Each model has `protected $fillable` array listing mass-assignable attributes
- **Never include**: `id`, `timestamps`, `foreign keys` (unless explicitly set)
- **Always use** `$fillable` (whitelist) not `$guarded` (blacklist) for security

### Timestamps

- Auto-managed by Eloquent
- `created_at` and `updated_at` on every model
- Cast to Carbon datetime objects automatically

## Controller Patterns

### Standard CRUD Controller

```php
// app/Http/Controllers/ResourceController.php

class ResourceController extends Controller
{
    public function index()        // GET /api/v1/resources
    public function store()        // POST /api/v1/resources
    public function show($id)      // GET /api/v1/resources/{id}
    public function update($id)    // PUT /api/v1/resources/{id}
    public function destroy($id)   // DELETE /api/v1/resources/{id}
}
```

### Response Format

- Always return JSON with `success` boolean
- Include `data` for resources
- Include `message` for operations
- Use appropriate HTTP status codes

## Authentication (Future)

- Register → generate JWT token
- Login → verify credentials, return JWT
- Protected routes → middleware checks `Authorization: Bearer <token>`
- Logout → token invalidation strategy TBD

## Authorization (Future)

- Employers can only manage their own job listings
- Employers can only review applications for their jobs
- Candidates can only view/manage their own applications
- Admins have full access

## Naming Conventions

### Files

- Controllers: `ResourceController.php` (singular, PascalCase)
- Models: `User.php` (singular, PascalCase)
- Migrations: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`

### Database

- Tables: `snake_case` plural (`job_listings`)
- Columns: `snake_case` (`job_listing_id`, `company_name`)
- Foreign keys: `{model}_id` (`user_id`, `employer_id`)

### Routes

- Plural, lowercase, hyphenated: `/api/v1/job-listings`
- Resource controllers auto-generate standard routes

### Methods

- camelCase: `getJobsByLocation()`, `createApplication()`

## Development Workflow

1. **Create Migration**: Define table schema
2. **Create Model**: Define relationships and fillable
3. **Create Controller**: Handle requests/responses
4. **Define Routes**: Map HTTP requests to controller actions
5. **Test**: Write feature tests for endpoints
6. **Document**: Update this file if schema changes

## Docker Setup

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run migrations
./vendor/bin/sail artisan migrate

# Rollback migrations
./vendor/bin/sail artisan migrate:rollback

# Fresh migrate (destroys data)
./vendor/bin/sail artisan migrate:refresh
```

## Testing

- Use Pest.php framework
- Feature tests in `tests/Feature/`
- Test API endpoints with HTTP requests
- Mock database with factories

## Future Enhancements

- [ ] Authentication (JWT/Sanctum)
- [ ] Authorization policies
- [ ] API resources for response transformation
- [ ] Search/filtering service
- [ ] File upload handling (resumes, logos)
- [ ] Email notifications
- [ ] Admin dashboard
- [ ] Rate limiting
- [ ] Logging & monitoring

## Important Notes

- All timestamps use ISO 8601 format in JSON responses
- All foreign keys use `constrained()->onDelete('cascade')` for referential integrity
- Unique constraints prevent duplicate applications/saved jobs
- Skills field stored as text - consider JSON if complex querying needed
