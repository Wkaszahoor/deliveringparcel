<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUkAndRecipientFieldsToBankAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('recipient_address', 255)->nullable()->after('account_title');
            $table->string('uk_account_number', 50)->nullable()->after('account_number');
            $table->string('uk_sort_code', 20)->nullable()->after('uk_account_number');
            $table->string('intermediary_bic', 20)->nullable()->after('swift_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['recipient_address', 'uk_account_number', 'uk_sort_code', 'intermediary_bic']);
        });
    }
}
