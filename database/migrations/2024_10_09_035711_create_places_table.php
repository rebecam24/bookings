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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->json('images')->nullable();
            $table->integer('capacity'); 
            $table->date('available_from')->nullable(); 
            $table->date('available_to')->nullable(); 
            $table->enum('type', ['salon', 'auditorio', 'sala de reunion', 'sala de conferencia'])->default('salon');
            $table->boolean('active')->default(true);
            $table->string('default_hours')->default('09:00-17:00');
            $table->json('default_days');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};