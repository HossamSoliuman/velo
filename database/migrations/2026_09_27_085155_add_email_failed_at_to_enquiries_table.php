<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Set when the enquiry email still fails after every retry.
     */
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->timestamp('email_failed_at')->nullable()->after('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('email_failed_at');
        });
    }
};
