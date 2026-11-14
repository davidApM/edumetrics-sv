<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión Académica y Carga Docente') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Administración de asignaturas, asignación de docentes titulares, secciones y períodos evaluativos.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- BLOQUE 1: ASIGNATURAS Y CARGA DOCENTE -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">1. Asignaturas y Carga Docente</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Registra nuevas materias y asigna el docente titular responsable.</p>
                </div>

                <!-- Formulario de Nueva Asignatura -->
                <div class="p-5 bg-gray-50 border-b border-gray-200">
                    <form method="POST" action="{{ route('academic.subjects.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase">Código</label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code') }}"
                                   placeholder="Ej. MAT-1B"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm font-mono uppercase">
                            @error('code')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase">Nombre de Asignatura</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Ej. Matemática I"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase">Grado / Nivel</label>
                            <select name="grade_level_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Seleccione...</option>
                                @foreach ($gradeLevels as $level)
                                    <option value="{{ $level->id }}" @selected((string) old('grade_level_id') === (string) $level->id)>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase">Docente Titular</label>
                            <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Sin asignar</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected((string) old('user_id') === (string) $teacher->id)>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-indigo-700">
                                + Agregar Materia
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Asignaturas -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-28">Código</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Asignatura</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Nivel / Grado</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Docente Titular</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Evaluaciones</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($subjects as $subject)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $subject->code }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $subject->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $subject->gradeLevel->name }}</td>
                                    <td class="px-4 py-3">
                                        @if ($subject->teacher)
                                            <span class="text-gray-800">{{ $subject->teacher->name }}</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800 font-medium">Sin docente</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $subject->assessments_count }}</td>
                                    <td class="px-4 py-3 text-right space-x-3">
                                        <a href="{{ route('academic.subjects.edit', $subject) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs uppercase">
                                            Editar / Reasignar
                                        </a>

                                        @if ($subject->assessments_count === 0)
                                            <form method="POST" action="{{ route('academic.subjects.destroy', $subject) }}"
                                                  onsubmit="return confirm('¿Eliminar la asignatura {{ $subject->name }}?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs uppercase">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-gray-500">No hay asignaturas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BLOQUE 2 Y 3: SECCIONES + PERÍODOS LECTIVOS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- BLOQUE 2: SECCIONES POR GRADO -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">2. Secciones por Grado</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Crea nuevas secciones (ej. "C") o consulta la matrícula por grupo.</p>
                    </div>

                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <form method="POST" action="{{ route('academic.sections.store') }}" class="flex flex-col sm:flex-row gap-3 items-end">
                            @csrf
                            <div class="flex-1 w-full">
                                <label class="block text-xs font-medium text-gray-700 uppercase">Grado</label>
                                <select name="grade_level_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    @foreach ($gradeLevels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-full sm:w-28">
                                <label class="block text-xs font-medium text-gray-700 uppercase">Letra / Nombre</label>
                                <input type="text"
                                       name="name"
                                       maxlength="10"
                                       placeholder="Ej. C"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm uppercase">
                            </div>

                            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                                + Crear Sección
                            </button>
                        </form>
                        @error('section_name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Grado</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Sección</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Alumnos Activos</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($sections as $section)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-800">{{ $section->gradeLevel->name }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-indigo-700">"{{ $section->name }}"</td>
                                        <td class="px-4 py-3 text-center text-gray-600">{{ $section->active_students_count }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if ($section->total_students_count === 0 && $section->assessments_count === 0)
                                                <form method="POST" action="{{ route('academic.sections.destroy', $section) }}"
                                                      onsubmit="return confirm('¿Eliminar Sección {{ $section->name }}?')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs uppercase">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('students.index', ['section_id' => $section->id]) }}"
                                                   class="text-xs font-semibold text-gray-500 hover:text-gray-800 uppercase">
                                                    Ver Nómina
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BLOQUE 3: PERÍODOS EVALUATIVOS -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">3. Períodos Evaluativos del Año Lectivo</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Normativa MINED: los períodos deben sumar exactamente 100%.</p>
                    </div>

                    <form method="POST" action="{{ route('academic.periods.update') }}" class="p-5 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-3">
                            @foreach ($periods as $idx => $period)
                                <div class="grid grid-cols-12 gap-3 items-center">
                                    <input type="hidden" name="periods[{{ $idx }}][id]" value="{{ $period->id }}">

                                    <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase">
                                        #{{ $period->period_order }}
                                    </div>

                                    <div class="col-span-6">
                                        <input type="text"
                                               name="periods[{{ $idx }}][name]"
                                               value="{{ old("periods.{$idx}.name", $period->name) }}"
                                               required
                                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </div>

                                    <div class="col-span-4 relative">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               max="100"
                                               name="periods[{{ $idx }}][weight_percentage]"
                                               value="{{ old("periods.{$idx}.weight_percentage", $period->weight_percentage) }}"
                                               required
                                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm text-right pr-7">
                                        <span class="absolute inset-y-0 right-2.5 flex items-center text-xs text-gray-400">%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                            <span class="text-xs text-gray-500">
                                Suma configurada: <strong>{{ number_format($periods->sum('weight_percentage'), 2) }}%</strong>
                            </span>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-indigo-700">
                                Guardar Períodos
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>