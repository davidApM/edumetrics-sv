<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Section;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradebookController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $calculationService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $isTeacherOnly = $user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador']);

        $subjects = Subject::with('gradeLevel')
            ->when($isTeacherOnly, fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $sections = Section::with('gradeLevel')
            ->whereIn('grade_level_id', $subjects->pluck('grade_level_id')->unique())
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $periods = Period::orderBy('period_order')->get();

        // Selección por defecto si el usuario entra sin filtros en la URL
        $selectedSubject = $request->filled('subject_id')
            ? $subjects->firstWhere('id', (int) $request->input('subject_id'))
            : $subjects->first();

        if ($request->filled('subject_id') && ! $selectedSubject) {
            abort(403, 'No tienes autorización para consultar el cuadro de notas de esta materia.');
        }

        $matchingSections = $selectedSubject
            ? $sections->where('grade_level_id', $selectedSubject->grade_level_id)->values()
            : collect();

        $selectedSection = $request->filled('section_id')
            ? $matchingSections->firstWhere('id', (int) $request->input('section_id'))
            : $matchingSections->first();

        if (! $selectedSection && $matchingSections->isNotEmpty()) {
            $selectedSection = $matchingSections->first();
        }

        $selectedPeriodParam = $request->input('period_id', $periods->first()?->id);
        $isAnnualMode = $selectedPeriodParam === 'annual';
        $selectedPeriod = ! $isAnnualMode
            ? $periods->firstWhere('id', (int) $selectedPeriodParam)
            : null;

        $matrix = null;

        if ($selectedSubject && $selectedSection) {
            if ($isAnnualMode) {
                $matrix = $this->calculationService->buildAnnualMatrix(
                    $selectedSubject,
                    $selectedSection,
                    $periods
                );
            } elseif ($selectedPeriod) {
                $matrix = $this->calculationService->buildPeriodMatrix(
                    $selectedSubject,
                    $selectedSection,
                    $selectedPeriod
                );
            }
        }

        return view('gradebook.index', [
            'subjects' => $subjects,
            'sections' => $sections,
            'periods' => $periods,
            'selectedSubject' => $selectedSubject,
            'selectedSection' => $selectedSection,
            'selectedPeriod' => $selectedPeriod,
            'isAnnualMode' => $isAnnualMode,
            'matrix' => $matrix,
        ]);
    }
}