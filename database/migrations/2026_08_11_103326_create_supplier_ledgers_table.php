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
        Schema::create('supplier_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained();
            $table->enum('type', ['purchase', 'payment', 'adjustment']);
            $table->foreignId('purchase_id')->nullable()->constrained();
            $table->decimal('debit', 12, 2)->default(0);   // aapne diya (payment)
            $table->decimal('credit', 12, 2)->default(0);  // aap par charha (purchase)
            $table->decimal('balance_after', 12, 2);        // running balance
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
        Schema::dropIfExists('supplier_ledgers');
    }
};
