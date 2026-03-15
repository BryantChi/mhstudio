<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            $table->text('description')->nullable()->after('content');
            $table->decimal('default_amount', 12, 2)->nullable()->after('description');
            $table->integer('order')->default(0)->after('default_amount');
        });
    }

    public function down(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            $table->dropColumn(['description', 'default_amount', 'order']);
        });
    }
};
