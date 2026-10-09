<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use RuntimeException;
use SQLite3;
use Throwable;

class ImportSqliteUsers extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:import-sqlite-users';
    protected $description = 'Copy existing user accounts from the local SQLite database into MySQL.';
    protected $usage       = 'db:import-sqlite-users';

    public function run(array $params)
    {
        if (! class_exists(SQLite3::class)) {
            CLI::error('The SQLite3 PHP extension is required to read the existing database.');

            return EXIT_ERROR;
        }

        $sqlitePath = WRITEPATH . 'webgis.sqlite';
        if (! is_file($sqlitePath)) {
            CLI::error('The existing SQLite database was not found at writable/webgis.sqlite.');

            return EXIT_ERROR;
        }

        $sqlite = new SQLite3($sqlitePath, SQLITE3_OPEN_READONLY);

        try {
            if ($sqlite->querySingle('PRAGMA integrity_check') !== 'ok') {
                throw new RuntimeException('The existing SQLite database failed its integrity check.');
            }

            $hasUsersTable = (int) $sqlite->querySingle(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'users'"
            );
            if ($hasUsersTable !== 1) {
                throw new RuntimeException('The existing SQLite database does not contain a users table.');
            }

            $db = Database::connect();
            if (strtolower($db->DBDriver) !== 'mysqli') {
                throw new RuntimeException('The default database connection must use MySQLi.');
            }

            if ($db->table('users')->countAllResults() !== 0) {
                throw new RuntimeException('The MySQL users table is not empty; no accounts were copied.');
            }

            $rows = $sqlite->query(
                'SELECT id, name, username, password_hash, password_salt, role, created_at, updated_at FROM users ORDER BY id'
            );
            if ($rows === false) {
                throw new RuntimeException('Could not read users from the existing SQLite database.');
            }

            if (! $db->transBegin()) {
                throw new RuntimeException('Could not start a MySQL transaction.');
            }

            $transactionStarted = true;
            $count = 0;

            while ($row = $rows->fetchArray(SQLITE3_ASSOC)) {
                if (! $db->table('users')->insert($row)) {
                    throw new RuntimeException('An account could not be copied into MySQL.');
                }

                $count++;
            }

            if (! $db->transStatus()) {
                throw new RuntimeException('The MySQL transaction failed; no accounts were copied.');
            }

            if (! $db->transCommit()) {
                throw new RuntimeException('The MySQL transaction could not be committed.');
            }

            $transactionStarted = false;
            CLI::write("Copied {$count} user account(s) into MySQL.", 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            if (isset($transactionStarted) && $transactionStarted) {
                $db->transRollback();
            }

            CLI::error($exception->getMessage());

            return EXIT_ERROR;
        } finally {
            $sqlite->close();
        }
    }
}
