<?php

namespace App\Commands;

use App\Libraries\PasswordHasher;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group       = 'Users';
    protected $name        = 'admin:create';
    protected $description = 'Create the first administrator account.';
    protected $usage       = 'admin:create';

    public function run(array $params)
    {
        $users = new UserModel();
        $username = strtolower(trim($this->prompt('Admin username')));
        $name = trim($this->prompt('Admin name'));

        if (! preg_match('/\A[a-zA-Z0-9_.-]{3,50}\z/', $username)) {
            CLI::error('Username must be 3-50 characters using letters, numbers, dot, underscore, or hyphen.');
            return EXIT_ERROR;
        }
        if ($name === '' || mb_strlen($name) > 100) {
            CLI::error('Admin name is required and must not exceed 100 characters.');
            return EXIT_ERROR;
        }
        if ($users->where('username', $username)->first() !== null) {
            CLI::error('That username already exists.');
            return EXIT_ERROR;
        }

        CLI::write('The password will be visible while typing. Choose a private terminal and enter at least 12 characters.');
        $password = $this->prompt('Admin password');
        if (mb_strlen($password) < 12 || mb_strlen($password) > 255) {
            CLI::error('Password must be between 12 and 255 characters. No account was created.');
            return EXIT_ERROR;
        }

        CLI::write('Creating the administrator account...');
        $credentials = PasswordHasher::hash($password);
        $userId = $users->insert([
            'name'          => $name,
            'username'      => $username,
            'password_hash' => $credentials['hash'],
            'password_salt' => $credentials['salt'],
            'role'          => 'admin',
        ]);
        if ($userId === false) {
            CLI::error('The administrator account could not be saved. No account was created.');
            return EXIT_ERROR;
        }

        CLI::write('Administrator created successfully.', 'green');
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
