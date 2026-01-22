# bunq-backend-assignment

A PHP backend application built for the **bunq Backend Coding Assignment**.  
The project provides a simple group-based messaging API implemented with **Slim Framework**, **SQLite**, and **PHPUnit**, and is fully testable via **Postman**.

This repository demonstrates clean architecture, RESTful API design, and automated testing.

---

## ✨ Features

- Create public groups
- Join existing groups
- Send messages to groups
- List messages in a group
- Simple user context handling via HTTP headers
- Fully covered with automated PHPUnit tests
- Postman collection included for manual API testing

---

## 🧱 Architecture Overview

The project follows a layered structure:

- **Application layer**: Actions, middleware, request/response handling
- **Domain layer**: Core business logic (User, Group, Message)
- **Infrastructure layer**: Persistence and repositories
- **Service layer**: Business orchestration
- **Tests**: Unit, integration, and feature tests

---

## 🛠 Tech Stack

- **PHP** 8+
- **Slim Framework 4**
- **SQLite** (file-based database)
- **PHPUnit** (unit & integration testing)
- **Postman** (manual API testing)

---

## 📦 Requirements

- PHP **8.0 or higher**
- Composer
- Git

---

## 🚀 Installation & Setup

Clone the repository:

```bash
git clone https://github.com/Mert-1262/bunq-backend-assignment.git
cd bunq-backend-assignment
Install dependencies:

bash
composer install
▶️ Running the Application
You can run the application using PHP’s built-in server:

bash
Kodu kopyala
php -S localhost:8080 -t public
The API will be available at:

arduino
Kodu kopyala
http://localhost:8080
🔐 Authentication / User Context
There is no login or registration system, as explicitly required by the assignment.

User identity is provided via the HTTP header:

pgsql
Kodu kopyala
X-User-Name: your-username
If the header is missing, the API responds with 400 Bad Request

Users are automatically created on first request

📡 API Endpoints (Summary)
Groups
POST /groups – Create a group

GET /groups – List all groups

POST /groups/{id}/join – Join a group

Messages
POST /groups/{id}/messages – Send a message to a group

GET /groups/{id}/messages – List messages in a group

🧪 Automated Tests (PHPUnit)
All core functionality is covered by PHPUnit tests.

Run the full test suite:

bash
Kodu kopyala
vendor/bin/phpunit
Test types included:

Unit tests (Domain & Repositories)

Integration tests (Actions)

Feature tests (API behavior & middleware)

📮 Postman Collection
A ready-to-use Postman collection is included to manually test the API.

Location:

bash
Kodu kopyala
/postman/Chat_Backend_API.postman_collection.json
How to use:
Open Postman

Import the collection file

Set the environment variable:

baseUrl → http://localhost:8080

username → any value (used for X-User-Name header)

Run requests in order (create group → join → send message → list messages)

📁 Project Structure
pgsql

app/            → Slim configuration (routes, middleware, settings)
src/
 ├── Application
 ├── Domain
 ├── Infrastructure
 ├── Repository
 └── Service
tests/          → PHPUnit tests
postman/        → Postman API collection
public/         → Application entry point
📝 Notes
The vendor/ directory is intentionally excluded from version control.

Database schema is automatically initialized for local usage.

CI workflow is included but local test results are the source of truth.

✅ Assignment Status
✔ All required backend features implemented
✔ PHPUnit tests completed successfully
✔ Postman tests provided
✔ Clean, maintainable codebase

👤 Author
Mert İyibiçer
Backend Engineering Candidate
bunq Coding Assignment
