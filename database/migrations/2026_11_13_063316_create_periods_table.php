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
    Schema::create('periods', function (Blueprint $table) {
        $table->id();
        $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
        $table->string('name', 50); // Ej: "Trimestre I" o "Período 1"
        $table->decimal('weight_percentage', 5, 2); // Ej: 33.33 o 25.00
        $table->unsignedTinyInteger('period_order');
        $table->timestamps();

        $table->unique(['academic_year_id', 'period_order']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
