<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $canManage = $user->hasAnyRole(['admin', 'coordinador']);

        $sectionsQuery = Section::with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name');

        if (! $canManage) {
            $gradeLevelIds = Subject::where('user_id', $user->id)
                ->pluck('grade_level_id')
                ->unique();

            $sectionsQuery->whereIn('grade_level_id', $gradeLevelIds);
        }

        $sections = $sectionsQuery->get();
        $allowedSectionIds = $sections->pluck('id');

        $search = trim((string) $request->input('search', ''));
        $sectionId = $request->input('section_id');
        $status = $request->input('status', 'active');

        $baseQuery = Student::with('section.gradeLevel')
            ->whereIn('section_id', $allowedSectionIds)
            ->when($sectionId, fn ($q) => $q->where('section_id', (int) $sectionId))
            ->when($search !== '', function ($q) use ($search) {
                $term = '%' . mb_strtolower($search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(nie) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(first_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                        ->orWhereRaw("LOWER(first_name || ' ' || last_name) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(last_name || ' ' || first_name) LIKE ?", [$term]);
                });
            });

        $totalActive = (clone $baseQuery)->where('is_active', true)->count();
        $totalInactive = (clone $baseQuery)->where('is_active', false)->count();

        $students = $baseQuery
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('section_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'sections' => $sections,
            'canManage' => $canManage,
            'stats' => [
                'active' => $totalActive,
                'inactive' => $totalInactive,
                'total' => $totalActive + $totalInactive,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeManagement($request);

        $sections = Section::with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        return view('students.create', [
            'sections' => $sections,
            'defaultSectionId' => $request->input('section_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);

        $validated = $request->validate([
            'nie' => ['required', 'string', 'max:20', 'unique:students,nie'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'nie.unique' => 'Ya existe un estudiante registrado con ese número de NIE.',
        ]);

        $student = Student::create([
            'nie' => trim($validated['nie']),
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'section_id' => $validated['section_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('students.index', ['section_id' => $student->section_id])
            ->with('success', "Estudiante {$student->full_name} matriculado correctamente.");
    }

    public function edit(Request $request, Student $student): View
    {
        $this->authorizeManagement($request);

        $sections = Section::with('gradeLevel')
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get();

        return view('students.edit', [
            'student' => $student,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeManagement($request);

        $validated = $request->validate([
            'nie' => ['required', 'string', 'max:20', Rule::unique('students', 'nie')->ignore($student->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'nie.unique' => 'Otro estudiante ya tiene asignado ese número de NIE.',
        ]);

        $student->update([
            'nie' => trim($validated['nie']),
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'section_id' => $validated['section_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('students.index', ['section_id' => $student->section_id])
            ->with('success', "Ficha de {$student->full_name} actualizada correctamente.");
    }

    public function toggleStatus(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeManagement($request);

        $student->update([
            'is_active' => ! $student->is_active,
        ]);

        $estado = $student->is_active ? 'activada' : 'inactivada';

        return back()->with('success', "Matrícula de {$student->full_name} {$estado}.");
    }

    private function authorizeManagement(Request $request): void
    {
        if (! $request->user()->hasAnyRole(['admin', 'coordinador'])) {
            abort(403, 'Solo Administración y Coordinación pueden gestionar la matrícula de estudiantes.');
        }
    }
}