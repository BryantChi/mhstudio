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
            $table->string('display_mode')->default('normal')->after('visibility');
            $table->boolean('hide_client')->default(false)->after('display_mode');
            $table->boolean('hide_results')->default(false)->after('hide_client');
            $table->string('confidential_label')->nullable()->after('hide_results');
            $table->string('abstract_color')->nullable()->after('confidential_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'display_mode',
                'hide_client',
                'hide_results',
                'confidential_label',
                'abstract_color',
            ]);
        });
    }
};
