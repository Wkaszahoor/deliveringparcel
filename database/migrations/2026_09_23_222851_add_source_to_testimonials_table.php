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
        Schema::table('testimonials', function (Blueprint $table) {
            // manual (admin-typed) | google | trustpilot | sitejabber
            $table->string('source', 20)->default('manual')->index()->after('country');
            $table->string('source_url')->nullable()->after('source');
            // Google review id (or a hash for other sources) — de-dupes re-imports.
            $table->string('external_id')->nullable()->after('source_url');
            $table->unique(['source', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->dropColumn(['source', 'source_url', 'external_id']);
        });
    }
};
