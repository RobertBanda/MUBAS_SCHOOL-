
# Copilot Instructions for Lilongwe Water Board Dashboard

## Project Architecture & Data Flow
- **Single-file dashboard:** All UI, business logic, and CRUD operations are in `index.php` (procedural PHP, inline HTML, MySQLi).
- **Database connection:** Centralized in `db.php` (included at the top of `index.php`).
- **Styling:** All CSS is in `style.css`.
- **No frameworks, routing, or separate backend files.**

## Key Conventions & Patterns
- **MySQL table names are always lowercase** (e.g., `customers`, `meters`, `staff`, `complaints`, `bills`, `payments`).
- **Each dashboard section is a tab:**
  - Structure: `<div class="tab">` containing a form (add/edit) and a table (display).
  - Example: See `customers` and `meters` sections in `index.php` for reference.
- **CRUD logic is inline:** All create/read/update/delete operations are handled directly in `index.php`.
- **Material Icons via CDN** for button icons.
- **Tab navigation:** Uses JavaScript `showTab` function.
- **No code should break existing working features.**

## Adding or Modifying Features
- **Follow the pattern of existing sections** (copy structure, naming, and logic style).
- **Add new tabs and forms** in the same style as `customers`/`meters`.
- **Use lowercase table names** in all SQL queries.
- **Do not modify or remove code for existing working sections.**
- **Test new features** to ensure the dashboard and all sections remain functional.

## Developer Workflows
- **Debugging:**
  - Enable error reporting in PHP for troubleshooting:
    ```php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ```
  - Blank screen: Usually an SQL error (often table name case mismatch).
  - Raw PHP in browser: Ensure server is running and PHP is enabled.
- **Database:** All required tables must exist before using a section. Create missing tables as needed.

## Integration Points & External Dependencies
- **Material Icons CDN** is used for UI icons (see `<link>` in `index.php`).
- **No external PHP libraries or frameworks.**

## File Reference
- `index.php`: Main dashboard (all UI, logic, CRUD)
- `db.php`: Database connection
- `style.css`: All styling

## AI Agent Guidance
- **Preserve all working features.**
- **Adhere strictly to project conventions and patterns.**
- **Document any new features or changes in this file.**
- **Ask for clarification if unsure about a convention or workflow.**

---
_Last updated: 2025-09-13_
