# Laravel Task Management API

This repository contains the implementation of a RESTful API for a simple task management system, developed using Laravel. The project follows best practices for structure, readability, and documentation.

## Installation

Follow the steps below to set up the project:

1. **Clone the Repository**
   ```bash
   git clone https://github.com/maqndon/sit.git
   cd sit
   ```

2. **Install Dependencies**
   Ensure you have [Composer](https://getcomposer.org/) installed.
   ```bash
   composer install
   ```

3. **Environment Configuration**
   Copy the `.env.example` file and configure your environment settings:
   ```bash
   cp .env.example .env
   ```
   Update the `.env` file with your database credentials and other environment variables.

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**
   Set up the database structure by running the migrations:
   ```bash
   php artisan migrate
   ```

7. **Seed the Database**
   Optionally, seed the database with sample data for manual testing:
   ```bash
   php artisan db:seed
   ```
   To run a specific seeder, such as the `TaskSeeder`:
   ```bash
   php artisan db:seed --class=TaskSeeder
   ```

8. **Run the Development Server**
   Start the Laravel development server:
   ```bash
   php artisan serve
   ```
   The application will be available at `http://127.0.0.1:8000`.

## Database Structure

The database for this project includes the following structure:

### Tasks Table
This table stores task information and includes the following fields:
- `id` (Primary Key): Auto-incrementing unique identifier.
- `title` (String): The title of the task, maximum 255 characters.
- `description` (Text): A detailed description of the task.
- `status` (Enum): The current status of the task. Values: `todo`, `in_progress`, `done`.
- `user_id` (foreignId): The user unique identifier.
- `created_at` (Timestamp): When the task was created.
- `updated_at` (Timestamp): When the task was last updated.

### Projects Table
This table stores project information and includes the following fields:
- `id` (Primary Key): Auto-incrementing unique identifier.
- `user_id` (foreignId): The user unique identifier.
- `name` (String): The title of the project, maximum 255 characters.
- `description` (Text): A detailed description of the project.
- `created_at` (Timestamp): When the project was created.
- `updated_at` (Timestamp): When the project was last updated.

## Implementation Details

- **Input Task Validation**:

  1. **Title and Description**:
     - The maximum length of the title is 255 characters.
     - Both title and description are required.
  
  2. **Status**:
     - The status can only take specific values: "todo", "in_progress", and "done".
  
  3. **Deadline**:
     - The deadline is a valid date if is set in the future.

- **Project Details**:
  - The project is built using **Laravel 11**.
  - **Pint** has been used to format the code, with the 'laravel' preset.
  - **Sanctum** has been implemented for API token authentication, allowing secure user authentication and authorization.
  - The results of the endpoints are paginated for better performance and usability.
  - A controller, service, requests (for update and store), and policies have been created for each model.
  - A trait has been implemented to create users with tasks and projects for testing purposes.
  - Enums have been implemented for `TaskStatus` and `User  Roles`.
  - Tests have been implemented using **Pest** with Describe Blocks for better organization and readability of test cases.
  - The requests that a user can make to the API are not restricted.

## Usage

This API allows users to manage tasks, projects and users with the following features:

- Create tasks
- Read tasks
- Update tasks
- Delete tasks

- Create projects
- Read projects
- Update projects
- Delete projects

- Create users
- Read users
- Update users
- Delete users

Refer to the documentation below for detailed API endpoints and request examples.

## API Documentation
The API follows RESTful principles. Below is a summary of the available endpoints:

| Method | Endpoint                        | Description                 |
|--------|---------------------------------|-----------------------------|
| GET    | `/api/tasks`                    | List all tasks              |
| GET    | `/api/tasks/overdue`            | List all overdue tasks      |
| POST   | `/api/tasks`                    | Create a new task           |
| GET    | `/api/tasks/{task}`             | Get details of a task       |
| PUT    | `/api/tasks/{task}`             | Update an existing task     |
| PATCH  | `/api/tasks/{task}/deadline`    | Update the task's deadline  |
| DELETE | `/api/tasks/{task}`             | Delete a task               |
| GET    | `/api/projects`                 | List all projects           |
| GET    | `/api/projects/{project}/tasks` | List all task's projects    |
| POST   | `/api/projects`                 | Create a new project        |
| GET    | `/api/projects/{project}`       | Get details of a project    |
| PUT    | `/api/projects/{project}`       | Update an existing project  |
| DELETE | `/api/projects/{project}`       | Delete a project            |

> Note: All requests require authentication. See the authentication section for details.

### Authentication
To test the API manually, you can use the following credentials:
- **Email**: `admin@admin.com`
- **Password**: `password`

#### Login Example

**cURL (Windows):**
```bash
curl --location --request POST "http://127.0.0.1:8000/api/login" ^
--header "Accept: application/json" ^
--form "email=\"admin@admin.com\"" ^
--form "password=\"password\""
```

**cURL (Linux/Mac):**
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/login' \
--header 'Accept: application/json' \
--form 'email="admin@admin.com"' \
--form 'password="password"'
```

#### Example Requests

**Get All Tasks (Windows):**
```bash
curl --location --request GET "http://127.0.0.1:8000/api/tasks" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Get All Tasks (Paginated) (Windows):**
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/tasks?page=2' ^
--header 'Authorization: Bearer Bearer the_token_from_login' ^
--header 'Accept: application/json" ^
--header 'Connection: keep-alive'
```

**Get All Tasks (Linux/Mac):**
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/tasks' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

**Get All Tasks (Paginated) (Linux/Mac):**
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/tasks?page=2' \
--header 'Authorization: Bearer Bearer the_token_from_login' \
--header 'Accept: application/json" \
--header 'Connection: keep-alive'
```

**Create a Task (Windows):**
```bash
curl --location --request POST "http://127.0.0.1:8000/api/tasks" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login" ^
--form "title=\"test1\"" ^
--form "description=\"description1\"" ^
--form "status=\"todo\""
```

**Create a Task (Linux/Mac):**
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/tasks' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login' \
--form 'title="test1"' \
--form 'description="description1"' \
--form 'status="todo"'
```

**Update a Task (Windows):**
```bash
curl --location --request PUT "http://127.0.0.1:8000/api/tasks/1" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login" ^
--data-urlencode "title=updated title"
```

**Update a Task (Linux/Mac):**
```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/tasks/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login' \
--data-urlencode 'title=updated title'
```

**Delete a Task (Windows):**
```bash
curl --location --request DELETE "http://127.0.0.1:8000/api/tasks/56" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Delete a Task (Linux/Mac):**
```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/tasks/56' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

**Get All Projects (Windows):**
```bash
curl --location --request GET "http://127.0.0.1:8000/api/projects" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Get All Projects (Linux/Mac)**
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/projects' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

**Get a Specific Project (Windows)**
```bash
curl --location --request GET "http://127.0.0.1:8000/api/projects/1" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Get a Specific Project (Linux/Mac)**
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/projects/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

**Create a Project (Windows)**
```bash
curl --location --request POST "http://127.0.0.1:8000/api/projects" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login" ^
--form "name=\"New Project\"" ^
--form "description=\"Project description\""
```

**Create a Project (Linux/Mac)**
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/projects' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login' \
--form 'name="New Project"' \
--form 'description="Project description"'
```

**Update a Project (Windows)**
```bash
curl --location --request PUT "http://127.0.0.1:8000/api/projects/1" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login" ^
--data-urlencode "name=Updated Project Name" ^
--data-urlencode "description=Updated project description"
```

**Update a Project (Linux/Mac)**
```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/projects/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login' \
--data-urlencode 'name=Updated Project Name' \
--data-urlencode 'description=Updated project description'
```

**Delete a Project (Windows)**
```bash
curl --location --request DELETE "http://127.0.0.1:8000/api/projects/1" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Delete a Project (Linux/Mac)**
```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/projects/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

**Get Tasks from a Specific Project (Windows)**
```bash
curl --location --request GET "http://127.0.0.1:8000/api/projects/1/tasks" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Get Tasks from a Specific Project (Linux/Mac)**
```bash
curl --location --request GET 'http://127.0.0.1:8000/api/projects/1/tasks' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

**Logout (Windows):**
```bash
curl --location --request POST "http://127.0.0.1:8000/api/logout" ^
--header "Accept: application/json" ^
--header "Authorization: Bearer the_token_from_login"
```

**Logout (Linux/Mac):**
```bash
curl --location --request POST 'http://127.0.0.1:8000/api/logout' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer the_token_from_login'
```

## Tests

Run the Pest tests to ensure all functionality is working as expected:
```bash
php artisan test
```

## Contributing

Feel free to fork the repository and submit pull requests. Make sure your changes follow Laravel best practices.

## License

This project is open-source and available under the [MIT license](LICENSE).
