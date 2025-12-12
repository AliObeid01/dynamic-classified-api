<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('field_id')->constrained('fields')->onDelete('cascade');
            $table->boolean('is_mandatory');
            $table->timestamps();

            $table->unique(['category_id','field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_fields');
    }
};