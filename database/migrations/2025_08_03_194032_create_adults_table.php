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
        Schema::create('adults', function (Blueprint $table) {
            $table->id('adult_id');
            $table->string('adult_first_name');
            $table->string('adult_last_name');
            $table->string('adult_email');
            $table->string('adult_street');
            $table->string('adult_hause_nr');
            $table->string('adult_city');
            $table->string('adult_tel_r');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adults');
    }
};
