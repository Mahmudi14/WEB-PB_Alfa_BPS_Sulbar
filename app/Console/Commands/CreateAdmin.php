<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'pb-alfa:create-admin';

    protected $description = 'Membuat akun Admin pertama';

    public function handle(): int
    {
        if (User::where('role', 'admin')->exists()) {
            $this->error('Admin sudah tersedia.');
            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama Admin'));
        $email = strtolower(trim((string) $this->ask('Email Admin')));

        $password = (string) $this->secret(
            'Password Admin minimal 12 karakter'
        );

        $confirmation = (string) $this->secret(
            'Ulangi password'
        );

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info('Akun Admin berhasil dibuat.');

        return self::SUCCESS;
    }
}
