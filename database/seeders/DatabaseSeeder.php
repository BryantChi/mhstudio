<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            CategorySeeder::class,
            PricingSeeder::class,
            ContractTemplateSeeder::class,
            ServiceSeeder::class,
            LegalPageSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ 資料庫種子資料建立完成!');
        $this->command->info('');
        $this->command->info('預設登入資訊:');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
        $this->command->warn('');
        $this->command->warn('⚠️  請立即修改預設密碼!');
        $this->command->info('');
    }
}
