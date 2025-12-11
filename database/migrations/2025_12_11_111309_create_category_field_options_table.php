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
            $table->foreignId('category_field_id')->constrained('category_fields')->onDelete('cascade');
            $table->foreignId('parent_option_id')->nullable()->constrained('category_field_options')->onDelete('cascade');
            $table->string('slug');
            $table->string('value');
            $table->string('label');
            $table->string('label_l1');
            $table->timestamps();

            $table->index(['category_field_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_field_options');
    }
};