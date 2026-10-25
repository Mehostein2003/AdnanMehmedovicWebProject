# AI Log

## Milestone 1: Setup, static frontend, ERD

**AI tool:** Claude (Sonnet 5.5) in the claude.ai chat interface.

**Used for:** project folder structure and `.gitignore`; static SPA (hash router, Bootstrap views, theme CSS); draft ERD (Mermaid); README draft; first draft of `AGENTS.md`. The student reviews and adjusts all of it and must be able to explain it.

**Most important prompts (student):**
1. "rebrand this bootstrap to be fully converted into redline autos a web app that I will add functionality to afterwards" (with the Grayscale theme HTML attached).

**Cases where the AI was wrong or produced poor code:**
1. *Fake success message.* In the first rebrand, `app.js` showed "You're on the list" after the newsletter form submit while the real request was commented out, so the page claimed something that never happened. Noticed when re-reading the code against the M1 scope ("no need to connect to the backend"). Fix: removed that form and its script. The M1 forms are static and `router.js` blocks their submit.