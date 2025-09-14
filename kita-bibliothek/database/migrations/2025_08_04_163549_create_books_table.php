<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id('book_id'); 
            $table->string('isbn', 50)->nullable()->unique();   
            $table->string('title');                            
            $table->string('author')->nullable();               
            $table->text('description')->nullable();           

            $table->enum('status', ['available','borrowed','reserved','lost'])
                  ->default('available');

            $table->enum('condition', ['new','used','damaged','missing_pages','repaired'])
                  ->default('new');

            $table->string('category_per_age')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
