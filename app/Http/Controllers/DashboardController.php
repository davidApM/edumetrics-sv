<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Period;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $calculationService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $isTeacherOnly = $user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador']);

        $periods = Period::orderBy('period_order')->get();
        $selectedPeriodParam = $request->input('period_id', $periods->first()?->id);
        $isAnnualMode = $selectedPeriodParam === 'annual';
        $selectedPeriod = ! $isAnnualMode
            ? ($periods->firstWhere('id', (int) $selectedPeriodParam) ?? $periods->first())
            : null;

        // Materias y secciones según el rol
        $subjects = Subject::with(['gradeLevel', 'teacher'])
            ->when($isTeacherOnly, fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $gradeLevelIds = $subjects->pluck('grade_level_id')->unique();

        $sections = Section::with('gradeLevel')
            ->whereIn('grade_level_id', $gradeLevelIds)
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        // Conteo de estudiantes activos por sección
        $activeStudentsBySection = Student::where('is_active', true)
            ->whereIn('section_id', $sections->pluck('id'))
            ->selectRaw('section_id, count(*) as total')
            ->groupBy('section_id')
            ->pluck('total', 'section_id');

        $totalStudents = (int) $activeStudentsBySection->sum();

        // Rendimiento consolidado por cada combinación Materia + Sección
        $performanceRows = collect();

        foreach ($subjects as $subject) {
            $matchingSections = $sections->where('grade_level_id', $subject->grade_level_id);

            foreach ($matchingSections as $section) {
                $matrix = $isAnnualMode
                    ? $this->calculationService->buildAnnualMatrix($subject, $section, $periods)
                    : ($selectedPeriod
                        ? $this->calculationService->buildPeriodMatrix($subject, $section, $selectedPeriod)
                        : null);

                if ($matrix) {
                    $performanceRows->push([
                        'subject' => $subject,
                        'section' => $section,
                        'summary' => $matrix['summary'],
                    ]);
                }
            }
        }

        // Filtrar grupos que tienen estudiantes y evaluaciones para promedios reales
        $evaluableRows = $performanceRows->filter(
            fn ($item) => $item['summary']['total_students'] > 0 && $item['summary']['planned_weight'] > 0
        );

        $overallAverage = $evaluableRows->isNotEmpty()
            ? round((float) $evaluableRows->avg(fn ($item) => $item['summary']['section_average']), 2)
            : 0.00;

        $totalEvaluatedEnrollments = $evaluableRows->sum(fn ($item) => $item['summary']['total_students']);
        $totalApprovedEnrollments = $evaluableRows->sum(fn ($item) => $item['summary']['approved_count']);

        $overallApprovalRate = $totalEvaluatedEnrollments > 0
            ? round(($totalApprovedEnrollments / $totalEvaluatedEnrollments) * 100, 1)
            : 0.0;

        // Evaluaciones con calificaciones pendientes en el período seleccionado (o en todo el año)
        $assessmentsQuery = Assessment::with(['subject.gradeLevel', 'section', 'period'])
            ->withCount('scores')
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->when(! $isAnnualMode && $selectedPeriod, fn ($q) => $q->where('period_id', $selectedPeriod->id))
            ->orderBy('due_date')
            ->orderByDesc('id');

        $allAssessments = $assessmentsQuery->get();

        $pendingAssessments = $allAssessments->filter(function (Assessment $assessment) use ($activeStudentsBySection) {
            $expectedStudents = (int) ($activeStudentsBySection[$assessment->section_id] ?? 0);

            return $expectedStudents > 0 && $assessment->scores_count < $expectedStudents;
        })->map(function (Assessment $assessment) use ($activeStudentsBySection) {
            $assessment->expected_students = (int) ($activeStudentsBySection[$assessment->section_id] ?? 0);

            return $assessment;
        })->values();

        return view('dashboard', [
            'isTeacherOnly' => $isTeacherOnly,
            'periods' => $periods,
            'selectedPeriod' => $selectedPeriod,
            'isAnnualMode' => $isAnnualMode,
            'kpis' => [
                'total_students' => $totalStudents,
                'total_subjects' => $subjects->count(),
                'total_sections' => $sections->count(),
                'overall_average' => $overallAverage,
                'approval_rate' => $overallApprovalRate,
                'pending_count' => $pendingAssessments->count(),
                'total_assessments' => $allAssessments->count(),
            ],
            'performanceRows' => $performanceRows,
            'pendingAssessments' => $pendingAssessments,
        ]);
    }
}