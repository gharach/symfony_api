# Symfony API Project
The Symfony 6 project integrates JWT authentication and API Platform v3 for secure API access. It includes PHPUnit for testing, fixtures for consistent test data, and validation for data integrity

This project demonstrates two approaches for building APIs with Symfony and API Platform.

## Branches Overview

- **`Main`**: Custom API logic using **UserController** for more control over routes, security, and operations.
- **`api_platform_3`**: Default **API Platform** behavior, using built-in conventions for resources, security, and operations.

**You can switch to the api_platform_3 branch to view the code related to API Platform 3 (without using the controller).**
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
2. Rename .env.example to .env and provide the required information.
3. Use fixtures to populate both the test database and the main database.
   php bin/console doctrine:fixtures:load 
   php bin/console doctrine:fixtures:load --env=test --no-interaction --append

##API Resource Operations
User Resource
GET /users, GET /users/{id}:
Available for all roles.

Access constraints:
ROLE_USER and ROLE_COMPANY_ADMIN can view only users within their company.

ROLE_SUPER_ADMIN can view all users.

POST /users:

Available for ROLE_COMPANY_ADMIN (can create ROLE_USER only with his company id) and ROLE_SUPER_ADMIN.

DELETE /users/{id}:

Available only for ROLE_SUPER_ADMIN.

Company Resource

GET /companies,

GET /companies/{id}:

Available for all roles.

POST /companies:

Available only for ROLE_SUPER_ADMIN.


**get the token using email and password:**

http://localhost:8000/api/login_check

body like:

{"email": "superadmin@admin.com", "password": "admin123"}

**create company:**
http://localhost:8000/api/companies

header : 
content type: application/ld+json
Bearer  token

body
{
"id": 107,
"name": "John Doezrfghz"
}

**create user :**
http://localhost:8000/api/users

header :
content type: application/ld+json
Bearer  token
body
{
"name": "John Doe z",
"email": "ali2wsd3@ali.com",
"password": "@Pass123454545",
"role": "ROLE_SUPER_ADMIN"
}

**delete user:**
http://localhost:8000/api/users/143

header :
content type: application/ld+json
Bearer  token

**get users:**

http://localhost:8000/api/users/

header :
content type: application/ld+json
Bearer  token

**get user by id:** 

http://localhost:8000/api/users/id

header :
content type: application/ld+json
Bearer  token

## Unit Test & Integration  Test

php bin/phpunit tests/Unit
php bin/phpunit tests/Integration/Api
