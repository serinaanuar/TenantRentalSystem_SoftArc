<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // relationships
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('user_id'); // user who paid

            // payment details (match your Payment model)
            $table->decimal('amount_paid', 10, 2);
            $table->string('method')->nullable();       // CARD | FPX | CASH
            $table->string('gateway')->nullable();      // optional internal gateway name
            $table->string('gateway_ref')->nullable();  // optional reference
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->timestamp('payment_date')->nullable();

            $table->timestamps();

            // foreign keys
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
