<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立工程師帳號
        $engineer = User::create([
            'name' => 'Bryant',
            'email' => 'bryantchi.work@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $engineer->assignRole('super-admin');
        // 建立超級管理員
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        $this->command->info('超級管理員已建立');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
        $this->command->warn('請立即修改預設密碼!');

        // 建立測試用戶 (可選)
        if (app()->environment('local')) {
            // 管理員
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin-user@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('admin');

            // 編輯
            $editor = User::create([
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $editor->assignRole('editor');

            // 作者
            $author = User::create([
                'name' => 'Author User',
                'email' => 'author@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $author->assignRole('author');

            $this->command->info('測試用戶已建立 (僅限本地環境)');
            $this->command->info('- admin-user@example.com (Admin)');
            $this->command->info('- editor@example.com (Editor)');
            $this->command->info('- author@example.com (Author)');
            $this->command->info('所有密碼: password');
        }
    }
}
