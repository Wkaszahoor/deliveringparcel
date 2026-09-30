<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplianceTables extends Migration
{
    public function up()
    {
        Schema::create('kyc_verifications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('doc_type', 30)->default('passport'); // passport|national_id|driver_license
            $t->string('doc_number')->nullable();
            $t->string('doc_country', 8)->nullable();
            $t->json('files')->nullable();                     // uploads/compliance/kyc/...
            $t->string('status', 20)->default('pending')->index(); // pending|approved|rejected|expired
            $t->text('rejection_reason')->nullable();
            $t->unsignedBigInteger('reviewed_by')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->date('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('sanctions_entries', function (Blueprint $t) {
            $t->id();
            $t->string('full_name')->index();
            $t->date('dob')->nullable();
            $t->string('country', 8)->nullable();
            $t->string('list_name', 100)->default('EU Consolidated');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('sanctions_screenings', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('subject_user_id')->nullable();
            $t->string('input_name');
            $t->integer('match_count')->default(0);
            $t->json('result')->nullable();
            $t->timestamps();
        });

        Schema::create('restricted_items', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('category', 100)->nullable();
            $t->string('severity', 20)->default('prohibited'); // prohibited|restricted
            $t->text('reason')->nullable();
            $t->boolean('requires_declaration')->default(false);
            $t->timestamps();
        });

        Schema::create('hs_codes', function (Blueprint $t) {
            $t->id();
            $t->string('code', 10)->unique();
            $t->string('description');
            $t->string('category', 100)->nullable();
            $t->decimal('duty_hint', 5, 2)->nullable();
            $t->timestamps();
        });

        Schema::create('vat_rules', function (Blueprint $t) {
            $t->id();
            $t->string('scheme', 30)->default('standard'); // eu_ioss|uk_vat|eu_b2b|standard
            $t->string('country_code', 8)->nullable();
            $t->decimal('rate', 5, 2)->default(0);
            $t->string('registration_number', 50)->nullable();
            $t->boolean('is_active')->default(true)->index();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('consent_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->index();
            $t->longText('body');
            $t->integer('version')->default(1);
            $t->boolean('is_active')->default(true);
            $t->string('trigger_type', 20)->default('always'); // always|country|route|cargo_type
            $t->string('trigger_value', 100)->nullable();
            $t->boolean('requires_signature')->default(true);
            $t->timestamps();
        });

        Schema::create('gdpr_exports', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->unsignedBigInteger('admin_id')->nullable();
            $t->integer('file_rows')->default(0);
            $t->timestamps();
        });
    }

    public function down()
    {
        foreach (['gdpr_exports', 'consent_templates', 'vat_rules', 'hs_codes', 'restricted_items', 'sanctions_screenings', 'sanctions_entries', 'kyc_verifications'] as $t) {
            Schema::dropIfExists($t);
        }
    }
}
