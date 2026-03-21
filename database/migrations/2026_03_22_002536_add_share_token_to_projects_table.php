<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('share_token', 32)->nullable()->unique()->after('exclude_from_search');
        });

        // 為所有現有作品生成 share_token
        $projects = \App\Models\Project::whereNull('share_token')->get();
        foreach ($projects as $project) {
            $project->update(['share_token' => \Illuminate\Support\Str::random(32)]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
