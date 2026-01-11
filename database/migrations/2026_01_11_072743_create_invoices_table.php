<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();

        // Tenant (user who pays)
        $table->unsignedBigInteger('user_id');

        // Related property
        $table->unsignedBigInteger('property_id')->nullable();

        $table->string('invoice_number')->unique();
        $table->decimal('amount', 10, 2);
        $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
        $table->date('due_date');
        $table->timestamp('paid_at')->nullable();

        $table->timestamps();

        // Foreign keys (match seniors' tables)
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('property_id')->references('id')->on('properties')->onDelete('set null');
    });
}
  


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
