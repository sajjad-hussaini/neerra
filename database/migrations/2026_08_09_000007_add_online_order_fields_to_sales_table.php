<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('sales', function (Blueprint $table) { $table->string('channel')->default('POS')->after('payment_method'); $table->string('customer_name')->nullable()->after('channel'); $table->string('customer_phone', 30)->nullable()->after('customer_name'); $table->text('delivery_address')->nullable()->after('customer_phone'); $table->string('status')->default('Completed')->after('delivery_address'); }); }
    public function down(): void { Schema::table('sales', function (Blueprint $table) { $table->dropColumn(['channel', 'customer_name', 'customer_phone', 'delivery_address', 'status']); }); }
};
