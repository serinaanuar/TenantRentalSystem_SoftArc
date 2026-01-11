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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id'); // tenant = user

            // Payment details
            $table->decimal('amount_paid', 10, 2);
            $table->string('method')->nullable(); // Card, FPX, etc.
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
