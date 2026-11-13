<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentRequest;
use App\Models\Assessment;
use App\Models\Period;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Assessment::with(['subject.gradeLevel', 'period.academicYear', 'section.gradeLevel'])
            ->withCount('scores')
            ->latest();

        // El docente solo ve evaluaciones de sus materias asignadas
        if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
            $query->whereHas('subject', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $assessments = $query->paginate(10)->withQueryString();
        $periods = Period::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->orderBy('period_order')
            ->get();

        $subjectsQuery = Subject::with('gradeLevel')->orderBy('name');
        if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
            $subjectsQuery->where('user_id', $user->id);
        }
        $subjects = $subjectsQuery->get();

        return view('assessments.index', compact('assessments', 'periods', 'subjects'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $periods = Period::whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->orderBy('period_order')
            ->get();

        $sections = Section::with('gradeLevel')
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->get();

        $subjectsQuery = Subject::with('gradeLevel')->orderBy('name');
        if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
            $subjectsQuery->where('user_id', $user->id);
        }
        $subjects = $subjectsQuery->get();

        return view('assessments.create', compact('periods', 'sections', 'subjects'));
    }

    public function store(StoreAssessmentRequest $request): RedirectResponse
    {
        Assessment::create($request->validated());

        return redirect()
            ->route('assessments.index')
            ->with('success', 'Evaluación creada correctamente.');
    }

    public function destroy(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('docente') && ! $user->hasAnyRole(['admin', 'coordinador'])) {
            abort_if($assessment->subject->user_id !== $user->id, 403);
        }

        $assessment->delete();

        return redirect()
            ->route('assessments.index')
            ->with('success', 'Evaluación eliminada correctamente.');
    }
}