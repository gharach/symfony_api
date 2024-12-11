# Symfony API Project
The Symfony 6 project integrates JWT authentication and API Platform v3 for secure API access. It includes PHPUnit for testing, fixtures for consistent test data, and validation for data integrity

This project demonstrates two approaches for building APIs with Symfony and API Platform.

## Branches Overview

- **`usercontroller`**: Custom API logic using **UserController** for more control over routes, security, and operations.
- **`api_platform_3`**: Default **API Platform** behavior, using built-in conventions for resources, security, and operations.

## Installation

### Prerequisites

Make sure you have the following tools installed:

- **PHP** (7.4 or higher)
- **Composer**
- **Symfony CLI** (optional, but recommended for easy management of Symfony projects)

### Steps

1. Clone the repository:

   ```
   git clone https://github.com/gharach/symfony_api.git
   cd symfony-api
Branch Details
usercontroller Branch
This branch implements custom API logic with UserController, giving full control over routes, security, and operations. You will define the behavior of API endpoints manually, allowing more customization and flexibility for your project.

Key Features:

Custom security rules and access control
Explicit handling of the creation, fetching, and deletion of users
Ability to modify or extend logic for user management
api_platform_3 Branch
This branch uses API Platform's default behavior for building a RESTful API. It leverages the built-in conventions of API Platform for automatic resource generation, pagination, filtering, and security.

Key Features:

Built-in API Platform security, including JWT authentication
Automatic handling of CRUD operations for resources
Pagination and filtering enabled by default
Less boilerplate code for rapid API development
Running Tests
Run PHPUnit tests to ensure everything is working correctly:



./vendor/bin/phpunit
Make sure to check the test output for any issues.

Postman API Endpoints
Here are some example API endpoints you can test using Postman:

Create User (POST): http://localhost:8000/api/users

Requires ROLE_SUPER_ADMIN or ROLE_COMPANY_ADMIN role.
Payload:
json

{
"name": "John Doe",
"password": "securepassword",
"role": "ROLE_USER",
"company": 1
}
Get User (GET): http://localhost:8000/api/users/{id}

Requires ROLE_SUPER_ADMIN or ROLE_COMPANY_ADMIN role, and the user must belong to the same company.
Get All Users (GET): http://localhost:8000/api/users

Requires ROLE_SUPER_ADMIN or ROLE_COMPANY_ADMIN role.
Delete User (DELETE): http://localhost:8000/api/users/{id}

Requires ROLE_SUPER_ADMIN role.