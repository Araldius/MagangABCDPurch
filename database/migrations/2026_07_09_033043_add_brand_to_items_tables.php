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
        Schema::table('items', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('specification');
        });

        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('specification');
        });

        Schema::table('quotation_details', function (Blueprint $table) {
            $table->string('offered_brand')->nullable()->after('offered_specification');
        });

        Schema::table('selection_items', function (Blueprint $table) {
            $table->string('final_brand')->nullable()->after('final_specification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('brand');
        });

        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn('brand');
        });

        Schema::table('quotation_details', function (Blueprint $table) {
            $table->dropColumn('offered_brand');
        });

        Schema::table('selection_items', function (Blueprint $table) {
            $table->dropColumn('final_brand');
        });
    }
};
