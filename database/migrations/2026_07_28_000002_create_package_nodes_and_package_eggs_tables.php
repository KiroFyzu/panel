<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageNodesAndPackageEggsTables extends Migration
{
    public function up(): void
    {
        Schema::create('package_nodes', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id');
            $table->unsignedInteger('node_id');
            $table->primary(['package_id', 'node_id']);
            $table->foreign('package_id')->references('id')->on('price_packages')->cascadeOnDelete();
            $table->foreign('node_id')->references('id')->on('nodes')->cascadeOnDelete();
        });

        Schema::create('package_eggs', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id');
            $table->unsignedInteger('egg_id');
            $table->primary(['package_id', 'egg_id']);
            $table->foreign('package_id')->references('id')->on('price_packages')->cascadeOnDelete();
            $table->foreign('egg_id')->references('id')->on('eggs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_eggs');
        Schema::dropIfExists('package_nodes');
    }
}