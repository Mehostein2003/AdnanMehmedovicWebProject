# AGENTS.md

Context file for AI coding assistants. Read before making changes.

## Project
Redline Autos: SPA for a used-car dealership. Customers browse vehicles, book test drives, write reviews. Admins manage all entities. Individual university project, delivered in 5 milestones. Only do the work of the current milestone.

## Stack
- Backend: pure PHP, FlightPHP (REST), PDO, MySQL, JWT. OpenAPI docs.
- Frontend: HTML, CSS, vanilla JS, Bootstrap 5. No PHP in the frontend.

## Architecture rules
- Backend layers: routes -> services -> dao. Routes hold no SQL or business logic. DAOs are the only code that uses PDO.
- Middleware (auth, role check, request logging, error handling) is reusable and registered in FlightPHP, never copied into routes.
- Frontend is a SPA: hash router in `frontend/js/router.js` loads templates from `frontend/views/`. No full page reloads.
- No backend code in the frontend and no frontend code in the backend.
- Secrets (DB credentials, JWT secret, API keys) live only in `backend/config/config.php`, which is git-ignored. Commit `config.example.php`.
- Never expose stack traces, SQL or file paths in API responses.

## Folder structure
```
backend/  routes/ services/ dao/ middleware/ config/ logs/(git-ignored)
frontend/ index.html  css/  js/  views/  assets/
docs/     ERD.md  AI_LOG.md
```

## Naming conventions
- DB tables: plural snake_case (`test_drives`). Columns: snake_case.
- PHP classes: PascalCase, one per file (`VehicleDao.php`, `VehicleService.php`).
- JS and HTML ids: camelCase. View files: lowercase (`vehicles.html`).
- REST routes: plural nouns (`/vehicles`, `/test-drives`).

## Rules for the AI
- Stay inside the current milestone. Do not add features from later milestones.
- Do not invent credentials, links or test data presented as real.
- Update `docs/AI_LOG.md` every milestone, including cases where the AI was wrong.
- Keep code simple enough for the student to explain line by line.
