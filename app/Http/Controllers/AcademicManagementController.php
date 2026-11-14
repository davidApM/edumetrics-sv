<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAcademicAdmin($request);

        $gradeLevels = GradeLevel::orderBy('id')->get();
        $teachers = User::role('docente')->orderBy('name')->get();

        $subjects = Subject::with(['gradeLevel', 'teacher'])
            ->withCount('assessments')
            ->when($request->filled('grade_level_id'), fn ($q) => $q->where('grade_level_id', $request->integer('grade_level_id')))
            ->when($request->filled('teacher_id'), fn ($q) => $q->where('user_id', $request->integer('teacher_id')))
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $sections = Section::with('gradeLevel')
            ->withCount([
                'students as active_students_count' => fn ($q) => $q->where('is_active', true),
                'students as total_students_count',
                'assessments',
            ])
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        $periods = Period::with('academicYear')
            ->orderBy('period_order')
            ->get();

        return view('academic.index', [
            'gradeLevels' => $gradeLevels,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'sections' => $sections,
            'periods' => $periods,
        ]);
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $this->authorizeAcademicAdmin($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:120'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'code.unique' => 'Ya existe una asignatura registrada con ese código.',
        ]);

        $subject = Subject::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'grade_level_id' => $validated['grade_level_id'],
            'user_id' => $validated['user_id'] ?? null,
        ]);

        return redirect()
            ->route('academic.index')
            ->with('success', "Asignatura «{$subject->name}» creada correctamente.");
    }

    public function editSubject(Request $request, Subject $subject): View
    {
        $this->authorizeAcademicAdmin($request);

        return view('academic.edit-subject', [
            'subject' => $subject,
            'gradeLevels' => GradeLevel::orderBy('id')->get(),
            'teachers' => User::role('docente')->orderBy('name')->get(),
        ]);
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizeAcademicAdmin($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'name' => ['required', 'string', 'max:120'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'code.unique' => 'Otra asignatura ya utiliza ese código.',
        ]);

        $subject->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'grade_level_id' => $validated['grade_level_id'],
            'user_id' => $validated['user_id'] ?? null,
        ]);

        return redirect()
            ->route('academic.index')
            ->with('success', "Asignatura «{$subject->name}» y carga docente actualizadas.");
    }

    public function destroySubject(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizeAcademicAdmin($request);

        if ($subject->assessments()->exists()) {
            return back()->with('error', "No se puede eliminar «{$subject->name}» porque ya tiene evaluaciones registradas.");
        }

        $name = $subject->name;
        $subject->delete();

        return redirect()
            ->route('academic.index')
            ->with('success', "Asignatura «{$name}» eliminada.");
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $this->authorizeAcademicAdmin($request);

        $validated = $request->validate([
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'name' => ['required', 'string', 'max:10'],
        ]);

        $sectionName = strtoupper(trim($validated['name']));

        $exists = Section::where('grade_level_id', $validated['grade_level_id'])
            ->whereRaw('UPPER(name) = ?', [$sectionName])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['section_name' => "La Sección \"{$sectionName}\" ya existe en el grado seleccionado."])
                ->withInput();
        }

        $data = [
            'grade_level_id' => $validated['grade_level_id'],
            'name' => $sectionName,
        ];

        if (Schema::hasColumn('sections', 'academic_year_id')) {
            $data['academic_year_id'] = AcademicYear::value('id');
        }

        Section::create($data);

        return redirect()
            ->route('academic.index')
            ->with('success', "Sección \"{$sectionName}\" creada correctamente.");
    }

    public function destroySection(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeAcademicAdmin($request);

        if ($section->students()->exists() || $section->assessments()->exists()) {
            return back()->with('error', "No se puede eliminar la Sección \"{$section->name}\" porque tiene estudiantes o evaluaciones asociadas.");
        }

        $label = "{$section->gradeLevel->name} — Sección \"{$section->name}\"";
        $section->delete();

        return redirect()
            ->route('academic.index')
            ->with('success', "{$label} eliminada.");
    }

    public function updatePeriods(Request $request): RedirectResponse
    {
        $this->authorizeAcademicAdmin($request);

        $validated = $request->validate([
            'periods' => ['required', 'array', 'min:1'],
            'periods.*.id' => ['required', 'integer', 'exists:periods,id'],
            'periods.*.name' => ['required', 'string', 'max:60'],
            'periods.*.weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $totalWeight = round(collect($validated['periods'])->sum(fn ($p) => (float) $p['weight_percentage']), 2);

        if (abs($totalWeight - 100.00) > 0.01) {
            return back()
                ->with('error', "La suma de los períodos debe ser exactamente 100.00% (actualmente suma {$totalWeight}%).")
                ->withInput();
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['periods'] as $periodData) {
                Period::where('id', $periodData['id'])->update([
                    'name' => trim($periodData['name']),
                    'weight_percentage' => $periodData['weight_percentage'],
                ]);
            }
        });

        return redirect()
            ->route('academic.index')
            ->with('success', 'Configuración de períodos y ponderaciones actualizada.');
    }

    private function authorizeAcademicAdmin(Request $request): void
    {
        if (! $request->user()->hasAnyRole(['admin', 'coordinador'])) {
            abort(403, 'Acceso restringido a Administración y Coordinación Académica.');
        }
    }
}