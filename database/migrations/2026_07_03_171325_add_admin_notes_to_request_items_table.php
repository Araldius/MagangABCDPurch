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
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->string('admin_notes')->nullable()->after('item_notes');
        });
        Schema::table('service_request_items', function (Blueprint $table) {
            $table->string('admin_notes')->nullable()->after('specification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn('admin_notes');
        });
        Schema::table('service_request_items', function (Blueprint $table) {
            $table->dropColumn('admin_notes');
        });
    }
};
