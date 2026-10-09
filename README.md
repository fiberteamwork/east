# WebGIS

The map frontend runs as a CodeIgniter 4 application. Static map layers, Excel
datasets, images, styles, and JavaScript stay in their existing project
directories and are served through authenticated CodeIgniter routes. User
accounts are stored in MySQL or MariaDB.

## Run with Laragon

Use PHP 8.2 or newer with the `mysqli` PHP extension. In Laragon, start Apache
and MySQL, create a database named `webgis` (for example, in phpMyAdmin), then
copy `.env.example` to `.env`. The example uses Laragon's default local MySQL
account (`root` with an empty password); change the values if your local MySQL
credentials differ. Open the application at `http://localhost/webgis/login`.
The project's root `index.php` forwards requests to CodeIgniter; map files and
datasets are served through authenticated routes. If Composer dependencies are
missing, install them from the project directory. Alternatively, start
CodeIgniter's standalone development server:

```powershell
php spark serve
```

The standalone server uses `http://localhost:8080/`; when using it instead of
Laragon Apache, set `app.baseURL` in `.env` to `http://localhost:8080/`.
Map assets and datasets are only available after signing in.

## Create the first administrator

Run the database migration and create an administrator account from an
interactive terminal. The password is visible while typing; use a private
terminal and choose a password of at least 12 characters.

```powershell
php spark migrate
php spark db:import-sqlite-users
php spark admin:create
```

If you cannot sign in to an existing administrator account, reset its password
from a private Laragon terminal with:

```powershell
php spark admin:password-reset
```

The command asks for the admin username and a new password; the password is
visible while typing. It changes only that account's password hash and salt.

`db:import-sqlite-users` is an optional one-time import for accounts in the
previous `writable/webgis.sqlite` file. It only copies accounts when the
MySQL `users` table is empty and never modifies the SQLite source.

There is no preset username or password. `admin:create` asks you to choose the
admin username, display name, and password; it does not print or store a
default password. The terminal confirms when the account is saved or displays
an error if it cannot be created. After creating the admin, sign in at
`http://localhost/webgis/login`. Administrators can manage the application from
the sidebar at `/admin`, including the FAT diagram, full map data, Excel uploads,
and user management.
The admin sidebar separates these features into individual pages.
User management supports assigning `user` or `admin` roles and viewing,
editing, or deleting accounts. The active administrator and the last remaining
administrator cannot be deleted or demoted.
Uploaded workbooks append the first sheet's rows to the original map data;
include Latitude and Longitude columns, and keep each file at or below 25 MB.
Regular accounts can sign in and use the map but cannot create other accounts.
Login, user creation, logout, and CSV/map-data writes are protected by CSRF.
Map-data workbook uploads are restricted to administrators; uploaded files are
stored under `writable/map_uploads` and served only to signed-in users.

## GitHub source repository

The public source repository intentionally excludes customer workbooks, CSV
exports, SQLite databases, local environment files, runtime uploads, and build
logs. To run the original map dataset locally, place an authorized copy of
`Data FAT Full.xlsx` at `data/Data FAT Full.xlsx`; do not commit operational map
data to a public repository.

For hosting, set `app.baseURL` in the private `.env` file to the URL where the
application is installed. Set `database.default.hostname`, `database.default.port`,
`database.default.database`, `database.default.username`, and
`database.default.password` to the MySQL/MariaDB connection details shown by the
hosting provider. Do not commit or upload `.env` to GitHub.

The previous `writable/webgis.sqlite` file is a SQLite database and cannot be
imported into MariaDB/phpMyAdmin as an SQL script. Keep it as a local backup.
Create the MySQL schema with `php spark migrate`; to retain its accounts, run
`php spark db:import-sqlite-users` once before creating any new users. This
command only imports into an empty MySQL `users` table.

## Password storage

Passwords are stored using SHA-256 and an independently generated, random
128-bit salt per account. The salt is stored separately beside the 64-character
hash and is included in verification. SHA-256 is a fast hash, not encryption,
and is not a password-specific key derivation function; use PHP
`password_hash()` and `password_verify()` for applications requiring stronger
resistance to offline password guessing.
