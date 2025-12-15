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
        Schema::create('order_source_token_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_source_token_id')->constrained('order_source_tokens');
            $table->unsignedBigInteger('expiration');
            $table->string('brand');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_source_token_cards');
    }
};
