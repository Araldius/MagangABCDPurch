<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->boolean('is_asset')->default(false);
            $table->string('purpose')->nullable();
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->renameColumn('plant', 'business_area');
        });

        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (Schema::hasColumn('service_requests', 'plant')) {
                    $table->renameColumn('plant', 'business_area');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropColumn(['is_asset', 'purpose']);
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->renameColumn('business_area', 'plant');
        });
        
        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (Schema::hasColumn('service_requests', 'business_area')) {
                    $table->renameColumn('business_area', 'plant');
                }
            });
        }
    }
};
