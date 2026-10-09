<?php

namespace App\Commands;

use App\Libraries\PasswordHasher;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ResetAdminPassword extends BaseCommand
{
    protected $group       = 'Users';
    protected $name        = 'admin:password-reset';
    protected $description = 'Set a new password for an existing administrator account.';
    protected $usage       = 'admin:password-reset';

    public function run(array $params)
    {
        $username = strtolower(trim($this->prompt('Admin username')));
        if (! preg_match('/\A[a-zA-Z0-9_.-]{3,50}\z/', $username)) {
            CLI::error('Username must be 3-50 characters using letters, numbers, dot, underscore, or hyphen.');

            return EXIT_ERROR;
        }

        $users = new UserModel();
        $user  = $users->where('username', $username)->first();
        if ($user === null || $user['role'] !== 'admin') {
            CLI::error('No administrator account was found with that username.');

            return EXIT_ERROR;
        }

        CLI::write('The password will be visible while typing. Use a private terminal.');
        $password = $this->prompt('New admin password');
        if (mb_strlen($password) < 12 || mb_strlen($password) > 255) {
            CLI::error('Password must be between 12 and 255 characters. No changes were made.');

            return EXIT_ERROR;
        }

        $credentials = PasswordHasher::hash($password);
        if (! $users->update($user['id'], [
            'password_hash' => $credentials['hash'],
            'password_salt' => $credentials['salt'],
        ])) {
            CLI::error('The new password could not be saved.');

            return EXIT_ERROR;
        }

        CLI::write('Administrator password updated successfully.', 'green');

        return EXIT_SUCCESS;
    }

    private function prompt(string $label): string
    {
        fwrite(STDOUT, $label . ': ');
        fflush(STDOUT);

        $input = fgets(STDIN);
        if ($input === false) {
            throw new \RuntimeException('No input received. Run this command in an interactive terminal.');
        }

        return rtrim($input, "\r\n");
    }
}
