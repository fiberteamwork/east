<?php

namespace App\Controllers;

use App\Libraries\PasswordHasher;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->has('user_id')) {
            return $this->redirectAfterLogin();
        }

        helper('form');

        if ($this->request->is('post')) {
            $username = strtolower(trim((string) $this->request->getPost('username')));
            $password = (string) $this->request->getPost('password');
            $user = (new UserModel())->where('username', $username)->first();

            if ($user !== null && PasswordHasher::verify($password, $user['password_salt'], $user['password_hash'])) {
                session()->regenerate(true);
                session()->set([
                    'user_id'  => (int) $user['id'],
                    'name'     => $user['name'],
                    'username' => $user['username'],
                    'role'     => $user['role'],
                ]);

                return $this->redirectAfterLogin();
            }

            return view('auth/login', [
                'error' => 'Username atau password salah.',
            ]);
        }

        return view('auth/login', ['error' => null]);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }

    private function redirectAfterLogin()
    {
        return redirect()->to(session()->get('role') === 'admin' ? '/admin/users' : '/');
    }
}
