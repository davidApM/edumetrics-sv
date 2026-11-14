<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class GradebookReportController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $calculationService
    ) {}

    public function sectionPdf(Request $request): Response
    {
        $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'period_id' => ['required'],
        ]);

        $user = $request->user();
        $isTeacherOnly = $user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador']);

        $subject = Subject::with(['gradeLevel', 'teacher'])->findOrFail($request->integer('subject_id'));
        $section = Section::with('gradeLevel')->findOrFail($request->integer('section_id'));

        if ($isTeacherOnly && (int) $subject->user_id !== (int) $user->id) {
            abort(403, 'No tienes autorización para exportar el acta de esta materia.');
        }

        if ((int) $subject->grade_level_id !== (int) $section->grade_level_id) {
            abort(422, 'La sección seleccionada no corresponde al grado de la materia.');
        }

        $periods = Period::with('academicYear')->orderBy('period_order')->get();
        $academicYear = $periods->first()?->academicYear;
        $periodParam = $request->input('period_id');
        $isAnnualMode = $periodParam === 'annual';
        $selectedPeriod = ! $isAnnualMode
            ? $periods->firstWhere('id', (int) $periodParam)
            : null;

        if (! $isAnnualMode && ! $selectedPeriod) {
            abort(404, 'Período académico no encontrado.');
        }

        $matrix = $isAnnualMode
            ? $this->calculationService->buildAnnualMatrix($subject, $section, $periods)
            : $this->calculationService->buildPeriodMatrix($subject, $section, $selectedPeriod);

        $pdf = Pdf::loadView('reports.section-gradebook', [
            'academicYear' => $academicYear,
            'subject' => $subject,
            'section' => $section,
            'selectedPeriod' => $selectedPeriod,
            'isAnnualMode' => $isAnnualMode,
            'matrix' => $matrix,
            'generatedBy' => $user,
            'generatedAt' => now(),
        ])->setPaper('letter', 'landscape');

        $periodSlug = $isAnnualMode ? 'anual' : Str::slug($selectedPeriod->name);
        $filename = sprintf(
            'acta-%s-sec-%s-%s.pdf',
            Str::slug($subject->name),
            Str::slug($section->name),
            $periodSlug
        );

        return $pdf->stream($filename);
    }

    public function studentPdf(Request $request, Student $student): Response
    {
        $user = $request->user();
        $isTeacherOnly = $user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador']);

        $section = Section::with('gradeLevel')->findOrFail($student->section_id);

        if ($isTeacherOnly) {
            $teachesGrade = Subject::where('user_id', $user->id)
                ->where('grade_level_id', $section->grade_level_id)
                ->exists();

            if (! $teachesGrade) {
                abort(403, 'No tienes autorización para emitir la boleta de este estudiante.');
            }
        }

        $periods = Period::with('academicYear')->orderBy('period_order')->get();
        $academicYear = $periods->first()?->academicYear;
        $report = $this->calculationService->buildStudentReportCard($student, $periods);

        $pdf = Pdf::loadView('reports.student-report-card', [
            'academicYear' => $academicYear,
            'report' => $report,
            'generatedBy' => $user,
            'generatedAt' => now(),
        ])->setPaper('letter', 'portrait');

        $filename = sprintf('boleta-%s-%s.pdf', $student->nie, Str::slug($student->full_name));

        return $pdf->stream($filename);
    }
}