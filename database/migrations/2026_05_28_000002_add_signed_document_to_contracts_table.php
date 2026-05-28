<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('signed_document_path')->nullable()->after('execution_method');
            $table->timestamp('signed_document_uploaded_at')->nullable()->after('signed_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['signed_document_path', 'signed_document_uploaded_at']);
        });
    }
};
