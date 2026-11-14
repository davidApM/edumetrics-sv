<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    private function authorizeManager(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user && $user->hasAnyRole(['admin', 'coordinador']), 403);
    }

    public function index(Request $request): View
    {
        $this->authorizeManager();

        $search = trim((string) $request->input('search', ''));
        $teacherColumn = Schema::hasColumn('subjects', 'teacher_id') ? 'teacher_id' : 'user_id';

        $teachers = User::role('docente')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function (User $teacher) use ($teacherColumn) {
                $teacher->assigned_subjects_count = Subject::where($teacherColumn, $teacher->id)->count();
                return $teacher;
            });

        return view('teachers.index', compact('teachers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Ya existe una cuenta registrada con ese correo electrónico.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $docenteRole = Role::firstOrCreate(['name' => 'docente']);

        $teacher = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $teacher->assignRole($docenteRole);

        return redirect()
            ->route('teachers.index')
            ->with('success', "Docente {$teacher->name} registrado. Ya puede iniciar sesión y ser asignado a asignaturas.");
    }

    public function edit(User $teacher): View
    {
        $this->authorizeManager();
        abort_unless($teacher->hasRole('docente'), 404);

        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        $this->authorizeManager();
        abort_unless($teacher->hasRole('docente'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Otro usuario ya utiliza este correo electrónico.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $teacher->update($data);

        return redirect()
            ->route('teachers.index')
            ->with('success', "Datos y credenciales de {$teacher->name} actualizados correctamente.");
    }

    public function destroy(User $teacher): RedirectResponse
    {
        $this->authorizeManager();
        abort_unless($teacher->hasRole('docente'), 404);

        $teacherColumn = Schema::hasColumn('subjects', 'teacher_id') ? 'teacher_id' : 'user_id';
        $assignedCount = Subject::where($teacherColumn, $teacher->id)->count();

        if ($assignedCount > 0) {
            return redirect()
                ->route('teachers.index')
                ->with('error', "No se puede eliminar a {$teacher->name} porque tiene {$assignedCount} asignatura(s) asignada(s). Reasígnelas primero en Gestión Académica.");
        }

        $name = $teacher->name;
        $teacher->delete();

        return redirect()
            ->route('teachers.index')
            ->with('success', "Cuenta docente de {$name} eliminada correctamente.");
    }
}
