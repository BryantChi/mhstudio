<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->enum('source', ['website', 'referral', 'social', 'cold_outreach', 'other'])->default('other');
            $table->enum('status', ['lead', 'active', 'inactive', 'archived'])->default('lead');
            $table->enum('tier', ['standard', 'premium', 'vip'])->default('standard');
            $table->text('notes')->nullable();
            $table->string('avatar')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('tier');
            $table->index('source');
        });

        Schema::create('client_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['note', 'call', 'email', 'meeting', 'other'])->default('note');
            $table->string('subject');
            $table->text('content')->nullable();
            $table->datetime('interaction_date');
            $table->timestamps();

            $table->index(['client_id', 'interaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_interactions');
        Schema::dropIfExists('clients');
    }
};
