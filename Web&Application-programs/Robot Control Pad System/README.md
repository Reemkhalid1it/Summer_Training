# Robot Control Pad System

An interactive control pad system designed to send movement commands to a robot, update its status in a MySQL database in real time, and allow external systems to fetch the latest recorded command.

This project is a simple but complete example of a **frontend ↔ backend ↔ database** flow: a webpage sends a button click to a PHP script, the script saves it in MySQL, and any other device (like a robot's microcontroller) can read that value at any time.

---

## 🔗 Project Files

| File | What it does |
|---|---|
| [`index.html`](./index.html) | The frontend page — the buttons the user clicks, plus the JavaScript that sends each click to the server. |
| [`update_command.php`](./update_command.php) | Backend script that receives a button command and saves it in the database. |
| [`get_state.php`](./get_state.php) | Backend script that returns the current stored command as JSON, so an external device (e.g. the robot itself) can read it. |
| [`db.example.php`](./db.example.php) | A safe **template** for the database connection settings. This is the file that's public on GitHub. |
| [`setup.sql`](./setup.sql) | The SQL script that creates the database table and its first row. Run once inside phpMyAdmin, never uploaded to the server. |

>  **Note for anyone cloning this repo:** you won't find a real `db.php` here on purpose — see the [Setup & Installation](#-setup--installation) section below to create your own from the template.

---

##  How It Works (step by step)

Think of it as a relay race with 3 runners: **the browser → PHP → the database.**

1. **Send Command** — When the user clicks a direction button in [`index.html`](./index.html), JavaScript's `fetch()` sends an **AJAX POST request** to [`update_command.php`](./update_command.php). AJAX just means "send data to the server in the background, without reloading the page" — that's why the status message updates instantly instead of the whole page refreshing.

2. **Database Update** — [`update_command.php`](./update_command.php) receives the button name (e.g. `"forward"`) and translates it into a single character using a lookup table, then runs an **UPDATE** query on the `robot_state` table (using a *prepared statement*, which is the safe way to insert data into SQL and avoid SQL injection):

   | Button | Stored as |
   |---|---|
   | `forward` | `f` |
   | `backward` | `b` |
   | `left` | `l` |
   | `right` | `r` |
   | `stop` | `S` |

   The table always has exactly **one row** (`id = 1`) — every new command overwrites the same row instead of creating a new one, since we only care about the *latest* command.

3. **Fetch State** — Whenever it needs to, an external device (like the robot's microcontroller) can call [`get_state.php`](./get_state.php), which reads that same row and returns it as JSON — the current command letter plus the timestamp of the last update.

4. **JSON everywhere** — Every PHP script here returns JSON (never plain HTML), because the JavaScript on the frontend expects to parse a JSON response with `res.json()`. This detail matters a lot — see the bug explanation below.

---

##  Setup & Installation

### 1. Create the database
In your hosting control panel (e.g. InfinityFree) → **MySQL Databases** → create a new database. Save these four values somewhere, you'll need them in step 3:
- Hostname
- Username
- Password
- Database name

### 2. Create the table
Open **phpMyAdmin** → select your database → go to the **SQL** tab → paste the entire content of [`setup.sql`](./setup.sql) → click **Go**.
This creates a `robot_state` table with a single starting row (`id = 1`, command = `"S"` for Stop).

### 3. Set up your local `db.php`
This repo intentionally does **not** include a real `db.php`, because it would contain your private database password. Instead:
1. Copy [`db.example.php`](./db.example.php) and rename the copy to `db.php`.
2. Open `db.php` and replace the placeholder values with your real credentials from step 1:
   ```php
   $host   = "sqlXXX.infinityfree.com";
   $user   = "epiz_XXXXXXXX";
   $pass   = "your_real_password";
   $dbname = "epiz_XXXXXXXX_control_db";
   ```
3. Keep this real `db.php` **out of GitHub** (see [Security Note](#-security-note) below).

### 4. Upload the files
Using your hosting's **File Manager** or an FTP client (e.g. FileZilla), upload these files into the same folder inside `htdocs`:
- `index.html`
- `db.php` (your real one, from step 3 — **not** `db.example.php`)
- `update_command.php`
- `get_state.php`

> Do **not** upload `setup.sql` — that file is only meant to be run once inside phpMyAdmin, it has no purpose sitting on the live server.

### 5. Test it
Open your site's URL in the browser (e.g. `yoursite.infinityfreeapp.com`) and click any button. You should see a confirmation message like:
```
Table updated: forward -> "f"
```
You can also double-check directly in phpMyAdmin by running:
```sql
SELECT * FROM robot_state;
```

---

## 🐞 The Bug We Found & Fixed

**Symptom:** clicking any button showed the error message:
> "An error occurred while connecting to the server, please try again"

**Root cause:** the original `db.php` checked for a failed connection the "old" way:
```php
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode([...]));
}
```
This pattern worked fine in older PHP versions. But **since PHP 8.1**, `mysqli` automatically **throws an exception** (`mysqli_sql_exception`) the moment the connection fails, instead of quietly filling in `$conn->connect_error`. That means the line `new mysqli(...)` itself crashes the script — execution never even reaches the `if` check below it.

When PHP crashes like this, it prints a raw HTML error page instead of the JSON the frontend expects. So on the JavaScript side, `fetch(...).then(res => res.json())` tries to parse that HTML as JSON, fails, and falls into `.catch()` — which is why the user only ever saw a generic, unhelpful error message with no real explanation.

**The fix** — three small changes:
1. **Wrap the connection in `try/catch`** inside `db.php`, so a failed connection returns a proper JSON error message instead of crashing the whole script:
   ```php
   try {
       $conn = new mysqli($host, $user, $pass, $dbname);
       $conn->set_charset("utf8mb4");
   } catch (mysqli_sql_exception $e) {
       header('Content-Type: application/json');
       echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
       exit;
   }
   ```
2. **Wrap the database queries** in [`update_command.php`](./update_command.php) and [`get_state.php`](./get_state.php) in `try/catch` too, so *any* database error — not just a connection failure — always comes back as valid JSON.
3. **Turn off raw PHP error output** (`display_errors = 0`) so that any unrelated warning can't sneak extra text into the JSON response and break it.

As a bonus, we also added `$conn->set_charset("utf8mb4")` so Arabic text is stored and displayed correctly, and made the frontend's error message in [`index.html`](./index.html) show the *actual* error text returned from PHP, instead of a generic message — which makes debugging future issues much faster.

**Bottom line:** the code fix alone isn't enough — you still need to put your **real** database credentials into `db.php` (step 3 above). Without that, you'll correctly see a clear "connection failed" message now, instead of a confusing generic one.

---

##  Security Note

`db.php` is listed in [`.gitignore`](./.gitignore) on purpose and is **not** part of this repository, because it contains real database credentials. Only [`db.example.php`](./db.example.php) — the placeholder template — is committed. If you fork or clone this project, always create your own `db.php` from the template and never commit it with real passwords.

---

##  Tech Stack
- HTML / CSS / Vanilla JavaScript (Fetch API, AJAX)
- PHP 8.1+ / `mysqli` with prepared statements
- MySQL (tested on InfinityFree hosting)
