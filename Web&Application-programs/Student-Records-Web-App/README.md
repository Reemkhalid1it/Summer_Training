# Student Records Web App

A simple web page that connects to a MySQL database using PHP. It lets a user submit a name and age, saves it to the database, displays all records in a live table, and lets the user toggle each record's status (0/1) instantly — without reloading the page.

## Live Demo
Hosted on InfinityFree (add your live URL here once uploaded).

## How It Works

The form and table live in one HTML page. When the user submits the form or clicks "Toggle", JavaScript sends a background request (AJAX, using fetch) to a PHP file. The PHP file talks to the MySQL database and sends back a JSON response, which JavaScript uses to update the page — so nothing ever fully reloads.

## Files

| File | Purpose |
|---|---|
| [index.html](./index.html) | Main page: the form, the table, and the JavaScript that ties everything together |
| [db.php](./db.php) | Database connection settings, shared by all PHP files |
| [insert.php](./insert.php) | Receives form data and inserts a new record into the stu table |
| [get.php](./get.php) | Fetches all records from the stu table and returns them as JSON |
| [toggle.php](./toggle.php) | Flips a record's status between 0 and 1 |
| [table.sql](./table.sql) | SQL to create the stu table (or add the status column to an existing one) |

## Database Setup

Run [table.sql](./table.sql) in phpMyAdmin before using the app. It creates a stu table with:

- id — auto-increment primary key
- name — the submitted name
- age — the submitted age
- status — 0 or 1, toggled by the button

## Step-by-Step Flow

1. User fills in Name and Age and clicks Submit.
2. JavaScript sends the data to insert.php via fetch, without reloading the page.
3. insert.php saves the record into the stu table (status starts at 0).
4. index.html calls get.php to load all records and displays them in the table below the form.
5. Clicking Toggle next to a record sends its id to toggle.php.
6. toggle.php flips the status in the database and returns the new value, which JavaScript uses to update just that row's Status cell — instantly, with no page refresh.

## Security Note: SQL Injection Fix

The original version of this project (in.php) inserted $_GET['name'] and $_GET['age'] directly into the SQL query string. This is a classic SQL injection vulnerability — a user could type something like '; DROP TABLE stu; -- into the name field and manipulate or damage the database.

This version fixes that by using prepared statements (mysqli::prepare + bind_param) in insert.php and toggle.php. With prepared statements, user input is never inserted directly into the SQL query text — it's bound as a separate parameter, so the database always treats it as plain data, never as executable SQL code.

## Tech Stack

- HTML / CSS for structure and styling
- JavaScript (fetch API) for AJAX requests
- PHP (mysqli, prepared statements) for the backend
- MySQL for data storage
- Hosted on InfinityFree
