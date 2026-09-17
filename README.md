# Multi-tenant Support Ticket API

A Laravel-based multi-tenant support ticket system with tenant isolation, authenticated REST APIs, asynchronous AI ticket summarization, billing webhook handling, and PHPUnit tests.

## Overview

This project was developed as part of the **Multi-tenant Support Ticket API** assignment.

The application provides:

* Multi-tenant ticket and user isolation
* Laravel Sanctum authentication
* REST APIs for ticket management
* Ticket assignment within a tenant
* Asynchronous AI summarization using Laravel queues
* Mock billing webhook with HMAC signature verification
* Webhook idempotency
* PHPUnit tests for tenant isolation and webhook idempotency
* Small Blade/Bootstrap interface for interacting with the APIs

---

# 1. Multi-tenancy

## Approach Chosen: Row-Level Tenant Scoping

I implemented **row-level multi-tenancy using `tenant_id`**.

Each tenant represents a client organization.

Users belong to a tenant:

```text
users
-----
id
name
email
password
tenant_id
```

Tickets also belong to a tenant:

```text
tickets
-------
id
tenant_id
created_by
assigned_to
title
description
status
priority
ai_summary
summary_status
```

The authenticated user's `tenant_id` is used to determine the current tenant.

The tenant ID is **not accepted from the API request body**. This prevents a user from attempting to create or access tickets belonging to another tenant.

### Tenant Isolation

The application uses tenant middleware to determine the tenant from the authenticated user.

For example:

```php
$tenantId = $request->user()->tenant_id;
```

Ticket queries are scoped using that tenant ID:

```php
Ticket::where('tenant_id', $tenantId)
    ->latest()
    ->get();
```

For operations involving a specific ticket, the application also verifies that the ticket belongs to the authenticated user's tenant.

If a ticket belongs to another tenant, the API returns:

```http
404 Not Found
```

This prevents exposing information about resources belonging to another tenant.

### Why I Chose Row-Level Tenancy

I chose row-level tenant scoping because:

1. It is simpler to implement within the assignment time limit.
2. It avoids maintaining separate database connections for every tenant.
3. It keeps migrations and deployments simpler.
4. It works well for a small-to-medium number of tenants.
5. Tenant isolation can still be enforced consistently through middleware and query scoping.

A separate database per tenant provides stronger physical isolation, but it requires additional infrastructure and operational work such as:

* Tenant database provisioning
* Dynamic database connection management
* Tenant-specific migrations
* Database monitoring
* Backup management
* Connection management
* Deployment/migration orchestration

For the 6–8 hour assignment, row-level isolation provides a good balance between implementation complexity and tenant isolation.

---

# 2. Scaling from 10 to 10,000 Tenants

If the application grows from approximately **10 tenants to 10,000 tenants**, I would consider moving towards stronger database isolation.

The direction I would take is:

```text
                    Load Balancer
                          |
            +-------------+-------------+
            |             |             |
        App Server    App Server    App Server
            |             |             |
            +-------------+-------------+
                          |
                  Tenant Routing Layer
                          |
          +---------------+---------------+
          |               |               |
      Tenant DB       Tenant DB       Tenant DB
```

### Database Isolation

For larger tenants or tenants with high traffic/data requirements, I would introduce database isolation.

Instead of storing every tenant's data in the same database, tenant data could be distributed across separate databases or database groups.

The application would determine the appropriate database based on the tenant.

This provides:

* Stronger data isolation
* Better control over database capacity
* Easier tenant-level backup/restore
* Ability to move high-volume tenants independently
* Reduced impact of a large tenant on other tenants

For 10,000 tenants, I would not necessarily create and operate 10,000 completely independent database servers. The exact strategy would depend on tenant size and traffic.

A practical approach could be:

```text
Small tenants
     ↓
Shared database / database cluster
     ↓
Partitioned or sharded by tenant

Large tenants
     ↓
Dedicated database
```

This allows infrastructure to scale based on tenant usage rather than treating every tenant identically.

### Horizontal Scaling

At the application level, I would introduce a load balancer and horizontally scale the application:

```text
                 Load Balancer
                       |
        +--------------+--------------+
        |              |              |
    Server 1       Server 2       Server 3
```

Application servers would remain stateless so that requests can be distributed across multiple servers.

Shared infrastructure such as Redis, queues, object storage and databases would be used where appropriate.

---

# 3. Authentication

The API uses **Laravel Sanctum** for authentication.

Users authenticate using their email and password and receive a Sanctum API token.

Authenticated API requests use:

```http
Authorization: Bearer <token>
```

Protected API routes use:

```php
Route::middleware([
    'auth:sanctum',
    'check.tenant',
    'check.subscription'
])->group(function () {
    // Protected routes
});
```

This ensures that:

1. The user is authenticated.
2. The user belongs to a tenant.
3. The tenant has an active subscription.

---

# 4. API Endpoints

## Authentication

### Login

```http
POST /api/login
```

Example request:

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

Response contains a Sanctum access token.

---

## Tickets

All ticket APIs require authentication.

### List Tickets

```http
GET /api/tickets
```

Returns tickets belonging only to the authenticated user's tenant.

---

### Create Ticket

```http
POST /api/tickets
```

Example:

```json
{
    "title": "Unable to login",
    "description": "The user is unable to access the application.",
    "priority": "high"
}
```

The tenant is automatically determined from the authenticated user.

The client does not provide `tenant_id`.

When a ticket is created, an asynchronous summarization job is dispatched.

---

### View Ticket

```http
GET /api/tickets/{id}
```

A ticket belonging to another tenant is not exposed.

---

### Update Ticket

```http
PUT /api/tickets/{id}
```

Example:

```json
{
    "title": "Updated issue",
    "description": "Updated description",
    "status": "in_progress",
    "priority": "high"
}
```

Supported statuses:

```text
open
in_progress
resolved
closed
```

Supported priorities:

```text
low
medium
high
```

---

### Assign Ticket

```http
PATCH /api/tickets/{id}/assign
```

Example:

```json
{
    "assigned_to": 5
}
```

The assigned user must belong to the same tenant as the ticket.

---

# 5. Asynchronous AI Summarization

Ticket creation should not wait for the AI summarization process.

The flow is:

```text
Client
  |
  | POST /api/tickets
  ↓
Create Ticket
  |
  | summary_status = pending
  |
  ↓
Dispatch Queue Job
  |
  ↓
Return API Response
  |
  |----------------------------|
                               |
                          Queue Worker
                               |
                               ↓
                    AI Summarization Service
                               |
                               ↓
                       Generate Summary
                               |
                               ↓
                    Update Ticket Record
                               |
                               ↓
                  summary_status = completed
```

The API returns immediately after the ticket is created.

The ticket initially contains:

```text
summary_status = pending
```

The queue worker later generates the summary and updates:

```text
ai_summary
summary_status = completed
```

If processing fails:

```text
summary_status = failed
```

### Mock AI Service

Since this assignment does not require integration with a real AI provider, the AI service simulates processing latency using a short delay.

Example:

```php
sleep(3);
```

It then generates a mock summary based on the ticket information.

This service is separated from the queue job so that it can later be replaced with an actual AI API integration.

### Running the Queue Worker

The application uses the database queue driver.

Run:

```bash
php artisan queue:work
```

---

# 6. Billing Webhook

A mock billing webhook endpoint is implemented:

```http
POST /api/webhooks/billing
```

The endpoint is public because a billing provider needs to be able to call it without a user's Sanctum token.

The webhook contains:

```json
{
    "id": "evt_12345",
    "type": "subscription.updated",
    "data": {
        "tenant_id": 1,
        "status": "active"
    }
}
```

---

# 7. Webhook Signature Verification

The webhook uses an HMAC SHA-256 signature.

The signature is generated from:

```text
raw request body + webhook secret
```

The client sends the signature using:

```http
X-Billing-Signature
```

The server generates its own signature:

```php
$expectedSignature = hash_hmac(
    'sha256',
    $payload,
    config('services.billing.webhook_secret')
);
```

The signatures are compared using:

```php
hash_equals(
    $expectedSignature,
    $signature
);
```

If the signature is invalid:

```http
401 Unauthorized
```

is returned.

This prevents unauthorized parties from modifying or sending fake billing events.

---

# 8. Webhook Idempotency

Payment providers can deliver the same webhook more than once.

To handle this, each webhook event has a unique event ID.

Example:

```text
evt_12345
```

The event ID is stored in a `webhook_events` table.

The table contains:

```text
id
event_id
event_type
processed_at
created_at
updated_at
```

`event_id` has a unique database constraint.

The processing flow is:

```text
Receive webhook
      |
      ↓
Verify signature
      |
      ↓
Check event_id
      |
      +---- Already processed ----> Return success
      |
      ↓
Find tenant
      |
      ↓
Update subscription status
      |
      ↓
Store webhook event
      |
      ↓
Return success
```

Therefore, if:

```text
evt_12345
```

is delivered twice, the second request does not process the subscription update again.

---

# 9. Subscription Middleware

Protected tenant APIs also check the tenant subscription status.

For example:

```text
subscription_status = active
```

allows access.

If the subscription is inactive, protected APIs return:

```http
403 Forbidden
```

The billing webhook remains publicly accessible so that it can update the subscription status and reactivate access.

---

# 10. Error Handling

The APIs return appropriate HTTP status codes.

Examples:

| Status | Meaning                                         |
| ------ | ----------------------------------------------- |
| 200    | Successful request                              |
| 201    | Resource created                                |
| 401    | Unauthenticated / invalid webhook signature     |
| 403    | Tenant/subscription access denied               |
| 404    | Resource not found or belongs to another tenant |
| 422    | Validation error                                |
| 500    | Unexpected server error                         |

Validation errors include the relevant validation messages.

---

# 11. Tests

PHPUnit tests are included for the core requirements.

## Test 1: Ticket Creation and Tenant Isolation

This test verifies that:

1. Tenant A can create a ticket.
2. The ticket is associated with Tenant A.
3. A user from Tenant A can see Tenant A's ticket.
4. A ticket belonging to Tenant B is not visible to Tenant A.
5. The ticket creation queue job is dispatched.

Example scenario:

```text
Tenant A
 └── User A
      └── Ticket A

Tenant B
 └── User B
      └── Ticket B
```

When User A requests the ticket list:

```text
Ticket A → Visible
Ticket B → Not visible
```

---

## Test 2: Webhook Idempotency

This test sends the same webhook event twice.

Example:

```text
evt_12345
```

The first request processes the event.

The second request is detected as a duplicate.

The test verifies that only one webhook event record exists.

This ensures duplicate webhook deliveries do not result in duplicate processing.

---

# 12. Database Design

Main tables:

```text
tenants
   |
   +---- users
   |
   +---- tickets

webhook_events
```

### Tenants

```text
id
name
subscription_status
created_at
updated_at
```

### Users

```text
id
name
email
password
tenant_id
created_at
updated_at
```

### Tickets

```text
id
tenant_id
created_by
assigned_to
title
description
status
priority
ai_summary
summary_status
created_at
updated_at
```

### Webhook Events

```text
id
event_id
event_type
processed_at
created_at
updated_at
```

Foreign keys and indexes are used to maintain referential integrity and improve query performance.

---

# 13. Local Setup

## Requirements

* PHP 8.2+
* Composer
* MySQL
* Node.js/NPM
* Laravel
* Database queue support

## Installation

Clone the repository:

```bash
git clone <repository-url>
cd <project-directory>
```

Install PHP dependencies:

```bash
composer install
```

Copy the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=support_ticket
DB_USERNAME=root
DB_PASSWORD=
```

Configure the queue:

```env
QUEUE_CONNECTION=database
```

Configure the webhook secret:

```env
BILLING_WEBHOOK_SECRET=super-secret-key
```

Run migrations:

```bash
php artisan migrate
```

If the project contains seed data:

```bash
php artisan db:seed
```

Start the Laravel application:

```bash
php artisan serve
```

Run the queue worker in another terminal:

```bash
php artisan queue:work
```

---

# 14. Testing

Run the PHPUnit test suite:

```bash
php artisan test
```

or:

```bash
vendor/bin/phpunit
```

The tests use an isolated test database.

---

# 15. Example API Flow

A typical ticket flow is:

```text
1. User logs in
        ↓
2. Sanctum token is generated
        ↓
3. User creates a ticket
        ↓
4. Tenant ID is taken from authenticated user
        ↓
5. Ticket is stored
        ↓
6. AI summary job is dispatched
        ↓
7. API returns immediately
        ↓
8. Queue worker processes the job
        ↓
9. AI summary is stored
        ↓
10. Client requests ticket again
        ↓
11. Client sees completed summary
```

---

# 16. Security Considerations

The following security measures are implemented:

* Laravel Sanctum authentication
* Tenant ID derived from the authenticated user
* Tenant isolation on ticket queries
* Tenant validation during ticket assignment
* Subscription validation middleware
* Webhook HMAC signature verification
* Constant-time signature comparison using `hash_equals`
* Webhook event idempotency
* Unique database constraint on webhook event IDs
* Request validation
* Protected ticket APIs

The client is never trusted to provide its own tenant ID.

---

# 17. Architecture Overview

```text
                    Client / Browser
                           |
                           ↓
                    Laravel API
                           |
                 Sanctum Authentication
                           |
                 Tenant Middleware
                           |
             Subscription Middleware
                           |
              +------------+------------+
              |            |            |
              ↓            ↓            ↓
           Tickets      Billing       Other
             API        Webhook        APIs
              |
              ↓
           MySQL
              |
              ↓
        Database Queue
              |
              ↓
        Queue Worker
              |
              ↓
      AI Summarization Service
              |
              ↓
        Update Ticket
```

---

# 18. Why This Architecture?

For this assignment, the primary goal was to demonstrate:

* Multi-tenant data isolation
* Secure API authentication
* Clean API design
* Asynchronous processing
* Webhook security
* Idempotent event processing
* Automated testing

Row-level tenant isolation was selected because it provides a straightforward implementation while maintaining strong application-level tenant boundaries.

The application is also structured so that individual components can later be replaced or scaled independently.

For example:

```text
Mock AI Service
       ↓
Real AI Provider

Database Queue
       ↓
Redis / Dedicated Queue Infrastructure

Row-Level Tenancy
       ↓
Database Isolation / Sharding
```

---

# 19. If I Had More Time

The assignment was intentionally kept within the requested 6–8 hour scope.

With additional development time, I would consider adding:

* Form Request classes for centralized validation
* API Resources for consistent API responses
* More comprehensive PHPUnit/feature tests
* Authorization policies
* Rate limiting
* Redis for queues and caching
* Real AI API integration
* Retry and failure handling for AI jobs
* Failed job monitoring
* More detailed API documentation using OpenAPI/Swagger
* Pagination and filtering improvements
* Structured application logging
* Docker configuration
* CI/CD pipeline
* More comprehensive frontend functionality
* Database sharding/isolation for larger tenants
* Horizontal application scaling
* Monitoring and alerting

---

# 20. Conclusion

This project demonstrates a Laravel-based multi-tenant support ticket system with:

* Row-level tenant isolation
* Sanctum authentication
* Ticket CRUD and assignment APIs
* Asynchronous AI summarization
* Secure billing webhooks
* Webhook idempotency
* Subscription-based access control
* PHPUnit feature tests
* A small web interface for interacting with the system

The current architecture is intentionally kept simple and maintainable for the assignment scope, while providing a clear path toward database isolation, horizontal scaling, and more distributed infrastructure as the number of tenants and overall traffic increases.
