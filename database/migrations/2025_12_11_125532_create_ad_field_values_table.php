<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_field_id')->constrained()->onDelete('cascade');
            $table->foreignId('selected_option_id')->nullable()->constrained('category_field_options')->onDelete('set null');
            $table->text('value');
            $table->timestamps();

            $table->unique(['ad_id', 'category_field_id']);
            $table->index('category_field_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_field_values');
    }
};