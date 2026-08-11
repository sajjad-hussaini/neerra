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
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->enum('type', ['sale', 'payment', 'adjustment']);
            $table->foreignId('sale_id')->nullable()->constrained();
            $table->decimal('debit', 12, 2)->default(0);   
            $table->decimal('credit', 12, 2)->default(0); 
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ledgers');
    }
};
