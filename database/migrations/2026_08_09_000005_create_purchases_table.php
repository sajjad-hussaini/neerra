<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('purchases', function (Blueprint $table) { $table->id(); $table->string('reference_number')->unique(); $table->string('supplier')->nullable(); $table->unsignedInteger('total'); $table->timestamp('purchased_at'); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('purchases'); }
};
