<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Password comes from the ADMIN_PASSWORD environment variable.
        // If none is set, a random one-time password is generated and printed
        // to the console (do NOT commit ADMIN_PASSWORD to .env in VCS).
        $randomlyGenerated = true;
        $password = env('ADMIN_PASSWORD');

        if (empty($password)) {
            $password = Str::random(16);
        } else {
            $randomlyGenerated = false;
        }

        User::updateOrCreate(
            ['email' => 'admin@zenxdownload.com'],
            [
                'name' => 'ZenX Admin',
                'password' => bcrypt($password),
                'is_admin' => 1
            ]
        );

        if ($randomlyGenerated) {
            \Illuminate\Support\Facades\Artisan::output(
                "Admin password generated for admin@zenxdownload.com: {$password} (store it securely; not shown again)"
            );
        }
    }
}
