<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('fields')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('category_field_options')->onDelete('cascade');
            $table->string('value');
            $table->string('label');
            $table->timestamps();

            $table->index(['value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_field_options');
    }
};