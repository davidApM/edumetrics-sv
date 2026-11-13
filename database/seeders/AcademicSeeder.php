<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::firstOrCreate(
            ['name' => '2026'],
            ['is_active' => true]
        );

        $periods = [
            ['name' => 'Período 1', 'weight_percentage' => 25.00, 'period_order' => 1],
            ['name' => 'Período 2', 'weight_percentage' => 25.00, 'period_order' => 2],
            ['name' => 'Período 3', 'weight_percentage' => 25.00, 'period_order' => 3],
            ['name' => 'Período 4', 'weight_percentage' => 25.00, 'period_order' => 4],
        ];

        foreach ($periods as $period) {
            Period::firstOrCreate(
                ['academic_year_id' => $year->id, 'period_order' => $period['period_order']],
                $period
            );
        }

        $grado1 = GradeLevel::firstOrCreate(['name' => '1.º Año de Bachillerato'], ['level_order' => 1]);
        $grado2 = GradeLevel::firstOrCreate(['name' => '2.º Año de Bachillerato'], ['level_order' => 2]);

        $sec1A = Section::firstOrCreate(
            ['academic_year_id' => $year->id, 'grade_level_id' => $grado1->id, 'name' => 'A'],
            ['capacity' => 35]
        );

        Section::firstOrCreate(
            ['academic_year_id' => $year->id, 'grade_level_id' => $grado1->id, 'name' => 'B'],
            ['capacity' => 35]
        );

        Section::firstOrCreate(
            ['academic_year_id' => $year->id, 'grade_level_id' => $grado2->id, 'name' => 'A'],
            ['capacity' => 35]
        );

        $docente = User::where('email', 'docente@edumetrics.sv')->first();

        $subjects = [
            ['code' => 'MAT-1B', 'name' => 'Matemática', 'grade_level_id' => $grado1->id, 'user_id' => $docente?->id],
            ['code' => 'FIS-1B', 'name' => 'Ciencias Naturales y Física', 'grade_level_id' => $grado1->id, 'user_id' => $docente?->id],
            ['code' => 'LEN-1B', 'name' => 'Lenguaje y Literatura', 'grade_level_id' => $grado1->id, 'user_id' => null],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject);
        }

        $students = [
            ['nie' => '20260101', 'first_name' => 'Sofía Valeria', 'last_name' => 'Alvarado Gómez'],
            ['nie' => '20260102', 'first_name' => 'Diego Alejandro', 'last_name' => 'Benítez Cruz'],
            ['nie' => '20260103', 'first_name' => 'Camila Fernanda', 'last_name' => 'Castillo Reyes'],
            ['nie' => '20260104', 'first_name' => 'Mateo Sebastián', 'last_name' => 'Domínguez Flores'],
            ['nie' => '20260105', 'first_name' => 'Valeria Isabel', 'last_name' => 'Escobar Hernández'],
            ['nie' => '20260106', 'first_name' => 'José Gabriel', 'last_name' => 'Fuentes López'],
            ['nie' => '20260107', 'first_name' => 'Daniela Nicole', 'last_name' => 'Guardado Mejía'],
            ['nie' => '20260108', 'first_name' => 'Andrés Eduardo', 'last_name' => 'Hernández Ortiz'],
            ['nie' => '20260109', 'first_name' => 'Gabriela Sofía', 'last_name' => 'Jiménez Pérez'],
            ['nie' => '20260110', 'first_name' => 'Luis Fernando', 'last_name' => 'López Quintanilla'],
        ];

        foreach ($students as $student) {
            Student::firstOrCreate(
                ['nie' => $student['nie']],
                array_merge($student, ['section_id' => $sec1A->id, 'is_active' => true])
            );
        }
    }
}