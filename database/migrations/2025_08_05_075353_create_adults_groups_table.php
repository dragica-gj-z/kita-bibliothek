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
        Schema::create('adults_groups', function (Blueprint $table) {
            $table->foreignId('adult_id')->constrained('adults', 'adult_id')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('kindergarten_groups', 'group_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adults_groups');
    }
};
