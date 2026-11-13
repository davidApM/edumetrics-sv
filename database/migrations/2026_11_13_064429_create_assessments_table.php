<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120); // Ej: "Actividad Integradora 1", "Examen Parcial"
            $table->decimal('weight_percentage', 5, 2); // Ej: 35.00
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE assessments ADD CONSTRAINT check_assessment_weight CHECK (weight_percentage > 0 AND weight_percentage <= 100)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};