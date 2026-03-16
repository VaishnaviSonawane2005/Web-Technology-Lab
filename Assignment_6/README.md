# Employee Management System (PHP + MySQL)

A professional, responsive web application to manage employees using PHP and MySQL (full CRUD operations: create, read, update, delete).

## Setup (XAMPP or MySQL Workbench)

1. **Start XAMPP** and ensure Apache + MySQL are running.
2. Place this project folder in your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\Assignment_6`).
3. Open **phpMyAdmin** or **MySQL Workbench** and import the database:
   - Import `init_db.sql` to create the `employee_db` database and `employees` table.
4. Open your browser and visit: http://localhost/Assignment_6/index.php

## Features

- **Tabbed Interface** — Single-page application with Dashboard, Employees, and Reports tabs
- **Dashboard with Statistics** — Overview with employee counts, departments, recent additions, and top lists
- **Employee Photo Upload** — Upload and display profile photos for employees
- **Search Employees** — Filter by name, email, department, or position
- **Pagination** — 10 employees per page with navigation
- **Add/Edit/Delete Employees** — Full CRUD with validation (unique email, required fields, dropdowns)
- **Reports with Charts** — Interactive pie charts for departments/positions and growth trends
- **Professional UI** — Modern design with gradients, icons, and responsive layout
- **Auto-generated IDs** — Employee IDs are automatically assigned by the database
- **Dynamic Interactions** — Modals for add/edit/delete, real-time updates
- **Responsive Design** — Mobile-friendly with Bootstrap 5 and Font Awesome icons

## Files

- `init_db.sql` — Database setup script
- `db.php` — PDO database connection
- `index.php` — Main application with tabbed interface (Dashboard, Employees, Reports)
- `uploads/` — Directory for employee photo files

## Technologies

- **Backend**: PHP 7+ with PDO for secure DB interactions
- **Frontend**: Bootstrap 5, Font Awesome icons, Chart.js for visualizations
- **Database**: MySQL with auto-incrementing IDs and file uploads
- **Security**: Input validation and prepared statements

## Notes

- Photos are stored in `uploads/` directory
- Email uniqueness enforced to prevent duplicates
- Fully responsive for mobile and desktop
- No login system - direct access to the application

Happy coding! 🚀
