<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 重置快取
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 建立權限
        $permissions = [
            // 用戶管理
            'view users',
            'create users',
            'edit users',
            'delete users',

            // 角色管理
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // 文章管理
            'view articles',
            'create articles',
            'edit articles',
            'delete articles',
            'publish articles',

            // 分類管理
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // 標籤管理
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',

            // SEO 管理
            'view seo',
            'edit seo',
            'generate sitemap',

            // 分析
            'view analytics',

            // 系統設定
            'view settings',
            'edit settings',

            // 媒體管理
            'view media',
            'upload media',
            'delete media',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 建立角色並分配權限

        // 超級管理員 - 擁有所有權限
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 管理員 - 大部分權限,但無法管理用戶和角色
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view articles',
            'create articles',
            'edit articles',
            'delete articles',
            'publish articles',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',
            'view seo',
            'edit seo',
            'generate sitemap',
            'view analytics',
            'view media',
            'upload media',
            'delete media',
        ]);

        // 編輯 - 只能編輯內容
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'view articles',
            'create articles',
            'edit articles',
            'view categories',
            'view tags',
            'view media',
            'upload media',
        ]);

        // 作者 - 只能管理自己的文章
        $author = Role::firstOrCreate(['name' => 'author']);
        $author->syncPermissions([
            'view articles',
            'create articles',
            'view categories',
            'view tags',
            'view media',
            'upload media',
        ]);

        // 訪客 - 只能查看
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'view articles',
            'view categories',
            'view tags',
        ]);

        $this->command->info('角色與權限已建立完成');
        $this->command->info('角色: super-admin, admin, editor, author, viewer');
        $this->command->info('權限數量: ' . count($permissions));
    }
}
