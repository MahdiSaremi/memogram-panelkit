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
        Schema::create('lock_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lock_require_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('unique_id');
            $table->timestamp('passed_at')->nullable();
            $table->timestamps();

            $table->unique(['lock_require_id', 'unique_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_statuses');
    }
};
