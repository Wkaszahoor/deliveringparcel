<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperKycDocumentsTable')) {
class CreateShipperKycDocumentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_kyc_documents')) {
            return;
        }
        Schema::create('shipper_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipper_profile_id');
            $table->enum('document_type', [
                'government_id', 'selfie_photo', 'address_proof',
                'social_media', 'consent_form',
            ]);
            $table->string('file_path', 500);
            $table->string('original_filename', 300)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('shipper_profile_id')
                ->references('id')->on('shipper_profiles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_kyc_documents');
    }
}
} // end class_exists guard
