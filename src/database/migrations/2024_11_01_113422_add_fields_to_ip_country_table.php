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
        Schema::table('ip_country', function (Blueprint $table) {
            $table->string('region')->nullable()->after('country');
            $table->string('subregion')->nullable()->after('region');
            $table->string('city')->nullable()->after('subregion');
            $table->string('timezone')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_country', function (Blueprint $table) {
            $table->dropColumn(['region', 'subregion', 'city', 'timezone']);
        });
    }
};