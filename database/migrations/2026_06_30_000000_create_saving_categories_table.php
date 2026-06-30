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
        Schema::create('saving_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->decimal('goal_amount', 15, 2); 
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->integer('duration')->default(1); 
            $table->string('unit')->default('Years'); 
            $table->string('frequency')->default('Monthly'); 
            $table->string('purpose_id')->nullable(); 
            $table->timestamps();
        });

        Schema::create('purpose', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_categories');
        Schema::dropIfExists('purpose');
    }
};
