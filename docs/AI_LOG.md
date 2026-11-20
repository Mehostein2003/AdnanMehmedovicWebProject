# AI Log

## Milestone 1: Setup, static frontend, ERD

**AI tool:** Claude (Sonnet 5.5) in the claude.ai chat interface.

**Used for:** project folder structure and `.gitignore`; static SPA (hash router, Bootstrap views, theme CSS); draft ERD (Mermaid); README draft; first draft of `AGENTS.md`. The student reviews and adjusts all of it and must be able to explain it.

**Most important prompts (student):**
1. "rebrand this bootstrap to be fully converted into redline autos a web app that I will add functionality to afterwards" (with the Grayscale theme HTML attached).

**Cases where the AI was wrong or produced poor code:**
1. *Fake success message.* In the first rebrand, `app.js` showed "You're on the list" after the newsletter form submit while the real request was commented out, so the page claimed something that never happened. Noticed when re-reading the code against the M1 scope ("no need to connect to the backend"). Fix: removed that form and its script. The M1 forms are static and `router.js` blocks their submit.

## Milestone 2: Database and DAO layer

**AI tool:** Claude (Sonnet 5.5) in the claude.ai chat interface.

**Used for:** MySQL schema and sample data (`database/redline_autos.sql`); `Database.php` (PDO connection); `BaseDao.php` and the five DAO classes (`UserDao`, `BrandDao`, `VehicleDao`, `TestDriveDao`, `ReviewDao`); `config.example.php`; the Milestone 2 updates to `docs/ERD.md`, `README.md` and `AGENTS.md`. The student reviews and adjusts all of it and must be able to explain it.

**Most important prompts (student):**
1. The Milestone 2 requirements text, with the project archive attached, and the instruction to do this milestone and nothing else.

**Cases where the AI was wrong or produced poor code:**
1. *Not tested by the AI.* The AI had no PHP or MySQL in its environment, so the SQL file and the DAO classes were written but never run. The student must import the SQL file and try each DAO method once before submitting.
2. *Sample password hash.* The AI could not generate a bcrypt hash, so all sample users use a hash copied from the PHP manual example (password `rasmuslerdorf`). It is only sample data, and the student should check it with `password_verify` or replace it.

