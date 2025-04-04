# System Patterns

*   **Architecture:** Simple monolithic PHP application.
    *   Frontend (HTML/CSS/JS) served directly by PHP.
    *   Backend logic handled by PHP scripts.
    *   MySQL database for storing tracking data.
*   **Key Technical Decisions:**
    *   Use of plain PHP, HTML, CSS, JS without major frameworks initially.
    *   Direct database interaction using PDO or MySQLi.
    *   `.htaccess` for routing/configuration.
*   **Design Patterns:** (To be identified/documented)
    *   Likely procedural approach in core scripts.
    *   Potential for basic MVC separation in `templates/` and `includes/`.
*   **Component Relationships:**
    *   `index.php` (main dashboard/UI)
    *   `install.php` (setup script)
    *   `transparent-pixel.php` (core tracking endpoint)
    *   `api/` (potential future API endpoints)
    *   `includes/` (shared code: config, DB connection, functions)
    *   `assets/` (static files: CSS, JS, images)
    *   `templates/` (HTML view components)
