<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('package_id');
            $table->unsignedInteger('node_id');
            $table->unsignedInteger('egg_id');
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('amount'); // paket price
            $table->unsignedBigInteger('fee')->nullable(); // gateway fee
            $table->unsignedBigInteger('total_payment'); // amount + fee
            $table->string('payment_method')->default('qris');
            $table->text('payment_number')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('status')->default('pending'); // pending, paid, expired, failed
            $table->timestamp('paid_at')->nullable();
            $table->unsignedInteger('server_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('package_id')->references('id')->on('price_packages')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('nodes')->cascadeOnDelete();
            $table->foreign('egg_id')->references('id')->on('eggs')->cascadeOnDelete();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
}