<?php

use App\Models\Customer;
use App\Models\Seller;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignIdFor(Seller::class);
            $table->foreignIdFor(Customer::class);
            $table->unsignedInteger('gross_amount');
            $table->char('currency', 3)->default('USD');
            $table->string('payment_provider')->default('stripe');
            $table->unsignedInteger('payment_provider_fee');
            $table->decimal('commission_rate');
            $table->unsignedInteger('commission_amount');
            $table->unsignedInteger('net_amount');
            $table->string('status');
            $table->string('idempotency_key')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
