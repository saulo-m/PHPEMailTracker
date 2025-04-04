# Tech Context

*   **Languages:** PHP, HTML, CSS, JavaScript, SQL (MySQL)
*   **Frameworks/Libraries:** None explicitly identified yet (likely plain implementations). Potential for jQuery or similar in `assets/js/script.js`.
*   **Database:** MySQL (assumed based on common PHP practices, needs confirmation from `includes/db.php` or `install.php`).
*   **Web Server:** Apache (implied by `.htaccess` files), but could be Nginx or others. PHP needs to be configured correctly.
*   **Development Setup:** Requires a local web server environment with PHP and MySQL (e.g., XAMPP, WAMP, MAMP, Docker).
*   **Technical Constraints:**
    *   Relies on email clients loading images. Some clients block images by default.
    *   Accuracy depends on unique image requests per open; caching can interfere.
    *   Requires server-side processing (PHP) and a database.
*   **Dependencies:** PHP interpreter, MySQL database server, Web server.
