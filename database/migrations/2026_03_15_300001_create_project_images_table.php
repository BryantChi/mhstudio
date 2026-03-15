<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_url');
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'order']);
        });

        // 將既有 cover_image 資料遷移到 project_images 表
        $projects = DB::table('projects')
            ->whereNotNull('cover_image')
            ->where('cover_image', '!=', '')
            ->get(['id', 'cover_image']);

        foreach ($projects as $project) {
            DB::table('project_images')->insert([
                'project_id' => $project->id,
                'image_url' => $project->cover_image,
                'alt_text' => null,
                'caption' => null,
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_images');
    }
};
