# Redline Autos

> Draft for Milestone 1. Sections marked TBD are filled in by the milestone shown.

## Project description
Redline Autos is a single-page web application for a used-car dealership in Orlando, FL. Customers browse vehicles, book test drives and leave reviews. Admins manage brands, vehicles, test drives, reviews and users.

Stack: PHP + FlightPHP REST API, MySQL (PDO), JWT auth, HTML/CSS/JS frontend with Bootstrap.

## Entities and relationships
Users, Brands, Vehicles, Test drives, Reviews. See the draft ERD in [docs/ERD.md](docs/ERD.md).
- A brand has many vehicles.
- A user books many test drives and writes many reviews.
- A vehicle has many test drives and many reviews.

## Roles and test accounts
Roles: `admin` and `customer`. Test accounts: TBD (Milestone 4).

## External API
Planned: NHTSA vPIC VIN decoder. The vehicle details page shows factory specs decoded from the vehicle's VIN (placeholder card already in `frontend/views/vehicle.html`). Implemented in Milestone 3.

## Run locally
- Frontend (static, Milestone 1): `cd frontend && python -m http.server 8000`, then open http://localhost:8000. A web server is required because pages are loaded with `fetch`.
- Database import and configuration file (`backend/config/config.php` from `config.example.php`): TBD (Milestone 2).

## Links
- GitHub repository: https://github.com/Mehostein2003/AdnanMehmedovicWebProject.git
- Swagger documentation: TBD (Milestone 3)
- Deployed application: TBD (Milestone 5)

## Folder structure
See [AGENTS.md](AGENTS.md).
