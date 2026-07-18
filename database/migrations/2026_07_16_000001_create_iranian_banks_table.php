<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('iranian-banks.table', 'iranian_banks'), function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_fa');
            $table->string('name_en');
            $table->json('card_prefixes');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('iranian-banks.table', 'iranian_banks'));
    }
};
