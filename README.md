# FreeNetly Control Panel

A simple PHP web control panel with a Tailwind CSS UI, database-backed auth, and an installer flow for Apache, PHP, MySQL, and phpMyAdmin.

## Features
- One-click installer page for database and initial admin setup
- Admin dashboard for user and service management
- User dashboard for account and service status
- Tailwind CSS CDN styling

## Quick start
1. Place this project in your web root, such as `C:/xampp/htdocs/freenetly`.
2. Start Apache and MySQL.
3. Open `http://localhost/freenetly/installer.php`.
4. Fill in the database details and create the initial admin account.

## GitHub deployment
- Push this folder to GitHub.
- On a server, clone it into your web root.
- Ensure Apache, PHP, MariaDB/MySQL, and phpMyAdmin are installed.
- Visit the installer page and complete setup.

## Notes
- The installer writes the database config to `includes/config.php`.
- The default admin credentials are set during installation using the requested values.
