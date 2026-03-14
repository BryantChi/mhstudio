<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->string('token', 64)->unique();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('project_type');
            $table->json('selected_features');
            $table->string('timeline');
            $table->string('budget');
            $table->decimal('estimated_min', 10, 2);
            $table->decimal('estimated_max', 10, 2);
            $table->string('currency', 10)->default('TWD');
            $table->string('status')->default('pending'); // pending, reviewing, quoted, accepted, rejected, expired
            $table->text('admin_notes')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};
