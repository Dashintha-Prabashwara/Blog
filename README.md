# Code & Canvas — Minimalist Blog (PHP + MySQL + JS)

A student project implementing a blog application for an assignment.

Design: minimalist black / white / gray palette with clean typography and responsive layout.

This repository contains a simple PHP + MySQL backend with HTML/CSS/JS frontend. It supports user registration/login, and CRUD for blog posts (Markdown editor + preview).

Setup (Windows, XAMPP):

1. Copy this project folder into your XAMPP `htdocs` directory (usually `C:\xampp\htdocs`).
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Create a MySQL database (example name: `code_canvas`) using phpMyAdmin (`http://localhost/phpmyadmin`).
4. Import the SQL schema located at `sql/create_tables.sql` via phpMyAdmin.
5. Update database credentials in `includes/db.php` to match your XAMPP MySQL settings (default user: `root`, password: empty).
6. In your browser, open `http://localhost/[your-folder-name]` (replace `[your-folder-name]` with the name of the project folder).

Notes:
- Passwords use PHP's `password_hash`/`password_verify`.
- The project uses a client-side markdown preview (marked.js) for convenience.
- Keep `includes/db.php` secure and do not commit credentials to public repos.

Files created by the assistant: index.php, view.php, editor.php, register.php, login.php, logout.php, create_post.php, update_post.php, delete_post.php, includes/*, assets/*, sql/create_tables.sql.


