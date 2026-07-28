<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodDaysToPricePackages extends Migration
{
    public function up(): void
    {
        Schema::table('price_packages', function (Blueprint $table) {
            $table->unsignedInteger('period_days')->default(30)->after('disk');
        });
    }

    public function down(): void
    {
        Schema::table('price_packages', function (Blueprint $table) {
            $table->dropColumn('period_days');
        });
    }
}
