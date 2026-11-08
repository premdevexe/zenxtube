<?php

namespace App\Services\Installer;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminAccountManager
{
    /**
     * Resolve the active User model class across Laravel versions.
     *
     * @return string
     * @throws Exception
     */
    public function resolveUserModel(): string
    {
        $configured = config('auth.providers.users.model');
        if (!empty($configured) && class_exists($configured)) {
            return $configured;
        }

        if (class_exists(\App\Models\User::class)) {
            return \App\Models\User::class;
        }

        if (class_exists(\App\User::class)) {
            return \App\User::class;
        }

        throw new Exception("Unable to resolve User model class in this Laravel application.");
    }

    /**
     * Create or update the primary administrator account.
     *
     * @param array $data ['name' => ..., 'email' => ..., 'password' => ...]
     * @return array
     * @throws Exception
     */
    public function createAdmin(array $data): array
    {
        $name = trim($data['name'] ?? 'Administrator');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("A valid administrator email address is required.");
        }

        if (strlen($password) < 8) {
            throw new Exception("Administrator password must be at least 8 characters long.");
        }

        $userModel = $this->resolveUserModel();

        // Check if users table exists
        if (!Schema::hasTable('users')) {
            throw new Exception("The 'users' database table does not exist. Run migrations first.");
        }

        // Determine if is_admin column exists
        $hasIsAdmin = Schema::hasColumn('users', 'is_admin');

        $attributes = [
            'name'       => $name,
            'password'   => Hash::make($password),
        ];

        if ($hasIsAdmin) {
            $attributes['is_admin'] = 1;
        }

        // Check if email_verified_at exists in newer Laravel schemas
        if (Schema::hasColumn('users', 'email_verified_at')) {
            $attributes['email_verified_at'] = date('Y-m-d H:i:s');
        }

        // Create or update administrator user
        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user = $userModel::updateOrCreate(
            ['email' => $email],
            $attributes
        );

        return [
            'success' => true,
            'user_id' => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'message' => "Administrator account '{$email}' configured successfully.",
        ];
    }
}

