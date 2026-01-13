<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // user who pays (no "tenant" actor, it's just user)
            $table->unsignedBigInteger('user_id');

            // related property (optional)
            $table->unsignedBigInteger('property_id')->nullable();

            // invoice details
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->date('due_date');

            // match model meaning: pending | paid | overdue
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');

            // optional convenience field
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
