<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->enum('type', ['service', 'maintenance', 'retainer', 'nda', 'other'])->default('service');
            $table->enum('status', ['draft', 'sent', 'signed', 'active', 'completed', 'cancelled'])->default('draft');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 10)->default('TWD');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index(['client_id', 'status']);
        });

        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['service', 'maintenance', 'retainer', 'nda', 'other'])->default('service');
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
        Schema::dropIfExists('contracts');
    }
};
