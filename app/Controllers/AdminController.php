<?php

namespace App\Controllers;

use App\Libraries\PasswordHasher;
use App\Models\UserModel;

class AdminController extends BaseController
{
    public function dashboard()
    {
        $directory = WRITEPATH . 'map_uploads';
        $uploadedFiles = is_dir($directory) ? (glob($directory . DIRECTORY_SEPARATOR . '*.xlsx') ?: []) : [];

        return view('admin/dashboard', [
            'currentPage'  => 'dashboard',
            'userCount'    => (new UserModel())->countAll(),
            'uploadCount'  => count($uploadedFiles),
        ]);
    }

    public function diagram()
    {
        return view('admin/diagram', ['currentPage' => 'diagram']);
    }

    public function data()
    {
        return view('admin/data', ['currentPage' => 'data']);
    }

    public function upload()
    {
        return view('admin/upload', [
            'currentPage'    => 'upload',
            'mapDataErrors'  => session()->getFlashdata('mapDataErrors') ?? [],
            'mapDataNotice'  => session()->getFlashdata('mapDataNotice'),
        ]);
    }

    public function users()
    {
        return view('admin/users', [
            'currentPage' => 'users',
            'users'       => (new UserModel())->orderBy('id', 'DESC')->findAll(),
            'errors'      => session()->getFlashdata('errors') ?? [],
            'notice'      => session()->getFlashdata('notice'),
        ]);
    }

    public function viewUser(int $id)
    {
        $user = (new UserModel())->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pengguna tidak ditemukan.');
        }

        return view('admin/user-view', [
            'currentPage' => 'users',
            'user'        => $user,
        ]);
    }

    public function editUser(int $id)
    {
        $user = (new UserModel())->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pengguna tidak ditemukan.');
        }

        return view('admin/user-edit', [
            'currentPage' => 'users',
            'user'        => $user,
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function updateUser(int $id)
    {
        $users = new UserModel();
        $user = $users->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pengguna tidak ditemukan.');
        }

        $data = [
            'name'     => trim((string) $this->request->getPost('name')),
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'role'     => (string) $this->request->getPost('role'),
            'password' => (string) $this->request->getPost('password'),
        ];
        $errors = [];

        if ($data['name'] === '' || mb_strlen($data['name']) > 100) {
            $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
        }
        if (! preg_match('/\A[a-zA-Z0-9_.-]{3,50}\z/', $data['username'])) {
            $errors[] = 'Username harus 3-50 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
        }
        if (! in_array($data['role'], ['user', 'admin'], true)) {
            $errors[] = 'Pilih peran user atau admin.';
        }
        if ($data['password'] !== '' && (mb_strlen($data['password']) < 12 || mb_strlen($data['password']) > 255)) {
            $errors[] = 'Password baru harus terdiri dari 12-255 karakter.';
        }

        $duplicate = $users->where('username', $data['username'])->where('id !=', $id)->first();
        if ($duplicate !== null) {
            $errors[] = 'Username tersebut sudah digunakan.';
        }

        $isCurrentUser = (int) session()->get('user_id') === $id;
        if ($isCurrentUser && $data['role'] !== 'admin') {
            $errors[] = 'Peran admin yang sedang digunakan tidak dapat diubah menjadi user.';
        }
        if ($user['role'] === 'admin' && $data['role'] !== 'admin' && $this->countOtherAdmins($id) === 0) {
            $errors[] = 'Admin terakhir tidak dapat diubah menjadi user.';
        }

        if ($errors !== []) {
            return redirect()->to('/admin/users/' . $id . '/edit')
                ->withInput()
                ->with('errors', $errors);
        }

        $update = [
            'name'     => $data['name'],
            'username' => $data['username'],
            'role'     => $data['role'],
        ];
        if ($data['password'] !== '') {
            $password = PasswordHasher::hash($data['password']);
            $update['password_hash'] = $password['hash'];
            $update['password_salt'] = $password['salt'];
        }

        if (! $users->update($id, $update)) {
            throw new \RuntimeException('Gagal memperbarui akun pengguna.');
        }
        if ($isCurrentUser) {
            session()->set('name', $data['name']);
            session()->set('username', $data['username']);
        }

        return redirect()->to('/admin/users')->with('notice', 'Data pengguna berhasil diperbarui.');
    }

    public function deleteUser(int $id)
    {
        $users = new UserModel();
        $user = $users->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pengguna tidak ditemukan.');
        }
        if ((int) session()->get('user_id') === $id) {
            return redirect()->to('/admin/users')->with('errors', ['Akun admin yang sedang digunakan tidak dapat dihapus.']);
        }
        if ($user['role'] === 'admin' && $this->countOtherAdmins($id) === 0) {
            return redirect()->to('/admin/users')->with('errors', ['Admin terakhir tidak dapat dihapus.']);
        }

        if (! $users->delete($id)) {
            throw new \RuntimeException('Gagal menghapus akun pengguna.');
        }

        return redirect()->to('/admin/users')->with('notice', 'Pengguna berhasil dihapus.');
    }

    public function createUser()
    {
        $data = [
            'name'     => trim((string) $this->request->getPost('name')),
            'username' => strtolower(trim((string) $this->request->getPost('username'))),
            'password' => (string) $this->request->getPost('password'),
            'role'     => (string) $this->request->getPost('role'),
        ];
        $errors = [];

        if ($data['name'] === '' || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Nama wajib diisi dan maksimal 100 karakter.';
        }
        if (! preg_match('/\A[a-zA-Z0-9_.-]{3,50}\z/', $data['username'])) {
            $errors['username'] = 'Username harus 3-50 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
        }
        if (mb_strlen($data['password']) < 12 || mb_strlen($data['password']) > 255) {
            $errors['password'] = 'Password harus terdiri dari 12-255 karakter.';
        }
        if (! in_array($data['role'], ['user', 'admin'], true)) {
            $errors['role'] = 'Pilih peran user atau admin.';
        }

        $users = new UserModel();
        if ($data['username'] !== '' && $users->where('username', $data['username'])->first() !== null) {
            $errors['username'] = 'Username tersebut sudah digunakan.';
        }

        if ($errors !== []) {
            return redirect()->to('/admin/users')->withInput()->with('errors', array_values($errors));
        }

        $password = PasswordHasher::hash($data['password']);
        $insertedId = $users->insert([
            'name'          => $data['name'],
            'username'      => $data['username'],
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
            'role'          => $data['role'],
        ]);
        if ($insertedId === false) {
            throw new \RuntimeException('Gagal membuat akun pengguna.');
        }

        return redirect()->to('/admin/users')->with('notice', 'Pengguna berhasil ditambahkan.');
    }

    private function countOtherAdmins(int $excludedId): int
    {
        return (new UserModel())
            ->where('role', 'admin')
            ->where('id !=', $excludedId)
            ->countAllResults();
    }
}
