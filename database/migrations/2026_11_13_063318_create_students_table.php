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
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->string('nie', 20)->unique(); // Número de Identificación Estudiantil
        $table->string('first_name', 100);
        $table->string('last_name', 100);
        $table->foreignId('section_id')->constrained()->restrictOnDelete();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
