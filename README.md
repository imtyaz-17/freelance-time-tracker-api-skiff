# Freelance Time Tracker API

A RESTful API built with Laravel 11 to help freelancers log, manage, and report their work hours across clients and projects.

## Features

- **Authentication** with Laravel Sanctum (register, login, logout)
- **Client Management** (CRUD operations)
- **Project Management** (CRUD operations)
- **Time Logging**
  - Manual time entry
  - Start/stop timer functionality
  - Billable/non-billable tracking
- **Comprehensive Reporting**
  - Filter by date range, client, or project
  - Group by day, week, month, project, or client
  - Calculate total hours worked
- **8+ Hours Notification** - Logs when a user records more than 8 hours in a day

## Database Structure

### Users
| Column             | Type       | Description                   |
|--------------------|------------|-------------------------------|
| id                 | bigint     | Primary key                   |
| name               | string     | User's full name              |
| email              | string     | User's email address (unique) |
| password           | string     | Hashed password               |
| email_verified_at  | timestamp  | When email was verified       |
| remember_token     | string     | For "remember me" functionality |
| created_at         | timestamp  | When record was created       |
| updated_at         | timestamp  | When record was last updated  |

### Clients
| Column         | Type       | Description                    |
|----------------|------------|--------------------------------|
| id             | bigint     | Primary key                    |
| user_id        | bigint     | Foreign key to users table     |
| name           | string     | Client name                    |
| email          | string     | Client email address           |
| contact_person | string     | Client contact person's name   |
| created_at     | timestamp  | When record was created        |
| updated_at     | timestamp  | When record was last updated   |

### Projects
| Column       | Type         | Description                     |
|--------------|--------------|----------------------------------|
| id           | bigint       | Primary key                      |
| client_id    | bigint       | Foreign key to clients table     |
| title        | string       | Project title                    |
| description  | text         | Project description              |
| status       | enum         | 'active' or 'completed'          |
| deadline     | date         | Project deadline (nullable)      |
| created_at   | timestamp    | When record was created          |
| updated_at   | timestamp    | When record was last updated     |

### Time Logs
| Column       | Type         | Description                     |
|--------------|--------------|----------------------------------|
| id           | bigint       | Primary key                      |
| project_id   | bigint       | Foreign key to projects table    |
| start_time   | datetime     | When time tracking started       |
| end_time     | datetime     | When time tracking ended (nullable) |
| description  | text         | Description of work done         |
| hours        | float        | Calculated hours worked          |
| is_billable  | boolean      | Whether time is billable         |
| created_at   | timestamp    | When record was created          |
| updated_at   | timestamp    | When record was last updated     |

## Entity Relationships

- **User** has many **Clients** (one-to-many)
- **Client** has many **Projects** (one-to-many)
- **Project** has many **Time Logs** (one-to-many)

## Prerequisites

- PHP 8.2+
- Composer
- MySQL 5.7+ or compatible database
- Git

## Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/freelance-time-tracker-api.git
cd freelance-time-tracker-api
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Create and configure environment file**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure your database in .env file**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freelance_tracker_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Run database migrations and seeders**
```bash
php artisan migrate --seed
```

6. **Start the development server**
```bash
php artisan serve
```

The API will be available at http://localhost:8000/api

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login and get token
- `POST /api/logout` - Logout (requires authentication)

### Clients
- `GET /api/clients` - List all clients
- `POST /api/clients` - Create a new client
- `GET /api/clients/{id}` - Get a specific client
- `PUT /api/clients/{id}` - Update a client
- `DELETE /api/clients/{id}` - Delete a client

### Projects
- `GET /api/projects` - List all projects
- `POST /api/projects` - Create a new project
- `GET /api/projects/{id}` - Get a specific project
- `PUT /api/projects/{id}` - Update a project
- `DELETE /api/projects/{id}` - Delete a project

### Time Logs
- `GET /api/time-logs` - List all time logs
- `POST /api/time-logs` - Create a new time log manually
- `GET /api/time-logs/{id}` - Get a specific time log
- `PUT /api/time-logs/{id}` - Update a time log
- `DELETE /api/time-logs/{id}` - Delete a time log
- `POST /api/time-logs/start` - Start a timer
- `POST /api/time-logs/{id}/stop` - Stop a timer

### Reports
- `GET /api/reports` - Generate reports with various filters:
  - `client_id` - Filter by client
  - `project_id` - Filter by project
  - `from` - Start date (required)
  - `to` - End date (required)
  - `group_by` - Group by day, week, month, project, or client (default: day)

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "message": "Success message describing the action",
  "data": { ... }
}
```

### Error Response
```json
{
  "message": "Error message describing what went wrong"
}
```

## Authentication

The API uses Laravel Sanctum for token-based authentication. To access protected endpoints:

1. Register a user or login to get a token
2. Include the token in the Authorization header of subsequent requests:
   ```
   Authorization: Bearer your_token_here
   ```

## Demo User

After seeding the database, you can login with the following credentials:
- Email: imtyaz@example.com
- Password: password

## Testing

Run the test suite using:
```bash
php artisan test
```
