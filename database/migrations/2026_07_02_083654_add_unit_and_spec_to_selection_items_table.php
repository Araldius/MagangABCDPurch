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
        Schema::table('selection_items', function (Blueprint $table) {
            $table->string('final_unit')->nullable()->after('final_quantity');
            $table->string('final_specification')->nullable()->after('final_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('selection_items', function (Blueprint $table) {
            $table->dropColumn(['final_unit', 'final_specification']);
        });
    }
};
