<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!class_exists('CreateShipperProofsTable')) {
class CreateShipperProofsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('shipper_proofs')) {
            return;
        }
        Schema::create('shipper_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('shipper_profile_id');
            $table->enum('proof_type', [
                'item_received', 'before_repack', 'after_repack',
                'dispatch_receipt', 'damage_report', 'purchase_receipt',
            ]);
            $table->string('file_path', 500); // PRIVATE disk — stream via authenticated route
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->boolean('admin_approved')->default(false);
            $table->boolean('customer_visible')->default(false);
            $table->timestamp('admin_approved_at')->nullable();
            $table->unsignedInteger('admin_approved_by')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('shipper_notes')->nullable();
            $table->timestamps();
            $table->foreign('assignment_id')
                ->references('id')->on('shipper_order_assignments')->onDelete('cascade');
            $table->index(['assignment_id', 'admin_approved', 'customer_visible'], 'sp_assign_appr_vis_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipper_proofs');
    }
}
} // end class_exists guard
