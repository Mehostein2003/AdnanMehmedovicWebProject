# Redline Autos

> Draft, updated in Milestone 3. Sections marked TBD are filled in by the milestone shown.

## Project description
Redline Autos is a single-page web application for a used-car dealership in Sarajevo, Bosnia and Herzegovina. Customers browse vehicles, book test drives and leave reviews. Admins manage brands, vehicles, test drives, reviews and users.

Stack: PHP + FlightPHP REST API, MySQL (PDO), JWT auth, HTML/CSS/JS frontend with Bootstrap.

## Entities and relationships
Users, Brands, Vehicles, Test drives, Reviews. See the ERD and schema notes in [docs/ERD.md](docs/ERD.md).
- A brand has many vehicles.
- A user books many test drives and writes many reviews.
- A vehicle has many test drives and many reviews.

## Roles and test accounts
Roles: `admin` and `customer`. Test accounts: TBD (Milestone 4).

## External API
NHTSA vPIC VIN decoder (free, no API key). The backend endpoint `GET /vehicles/{id}/specs` takes the VIN of a vehicle, calls the NHTSA API and returns factory specs (make, model, year, body class, engine, fuel, drive type, plant). The vehicle details page will show them in the "Factory specs from VIN" card (placeholder in `frontend/views/vehicle.html`); the frontend is connected in Milestone 4.

The request has a timeout (settings in `backend/config/config.php`, see `config.example.php`). If the API fails, the failure is written to `backend/logs/error.log` and the endpoint returns a 503 with a friendly message; the rest of the API keeps working. Note: the VINs in the sample data are made up, so NHTSA may return partial or no data for them. Use a real VIN (via `PUT /vehicles/{id}`) to see full specs.

## Run locally
- Frontend (static, Milestone 1): `cd frontend && python -m http.server 8000`, then open http://localhost:8000. A web server is required because pages are loaded with `fetch`.
- Database (Milestone 2, needs MySQL 8.0.16 or newer):
  1. From PowerShell, import the schema and sample data with `Get-Content .\database\redline_autos.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u YOUR_DB_USER -p` (replace `YOUR_DB_USER` with your MySQL username; this creates the `redline_autos` database).
  2. Copy `backend/config/config.example.php` to `backend/config/config.php` and enter your database credentials. `config.php` is git-ignored.
  3. The DAO classes are in `backend/dao/`. They are used by the services in `backend/services/`.
- Backend API (Milestone 3, needs PHP 8.0 or newer with the curl and pdo_mysql extensions, and Composer):
  1. `cd backend` and run `composer install` (installs FlightPHP into `backend/vendor/`, which is git-ignored).
  2. If your existing `backend/config/config.php` has no `external_api` block, copy it from `config.example.php` (defaults are used if it is missing).
  3. Start the API with `php -S localhost:8080 index.php` and open http://localhost:8080/vehicles.
  4. Swagger documentation: http://localhost:8080/swagger/index.html.
  5. With XAMPP/Apache, put the project in `htdocs`; `backend/.htaccess` sends all requests to `index.php` (mod_rewrite must be enabled).

## Links
- GitHub repository: https://github.com/Mehostein2003/AdnanMehmedovicWebProject.git
- Swagger documentation: http://localhost:8080/swagger/index.html (local, see "Run locally"; the OpenAPI file is `backend/swagger/openapi.json`)
- Deployed application: TBD (Milestone 5)

## API overview
CRUD endpoints for all five entities (`/users`, `/brands`, `/vehicles`, `/test-drives`, `/reviews`): `GET` list, `GET /{id}`, `POST`, `PUT /{id}`, `DELETE /{id}`, plus `GET /vehicles/{id}/specs` (external API). Responses are JSON with status codes 200, 201, 400, 404, 500 (and 503 if the external API is down). Authentication and role checks (401/403) are added in Milestone 4, so the endpoints are open for now.

## Folder structure
See [AGENTS.md](AGENTS.md).
