<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Panel Académico — EduMetrics SV') }}
                    </h2>
                    <span class="px-2.5 py-0.5 text-xs font-semibold uppercase rounded-full bg-indigo-100 text-indigo-800">
                        {{ auth()->user()->getRoleNames()->first() ?? 'Usuario' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Bienvenido, {{ auth()->user()->name }}.
                    {{ $isTeacherOnly ? 'Resumen de tus asignaturas y secciones asignadas.' : 'Supervisión académica general de la institución.' }}
                </p>
            </div>

            <!-- Filtro rápido de período -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                <select name="period_id"
                        onchange="this.form.submit()"
                        class="rounded-md border-gray-300 shadow-sm text-sm">
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected(! $isAnnualMode && $selectedPeriod?->id === $period->id)>
                            {{ $period->name }} ({{ number_format($period->weight_percentage, 0) }}%)
                        </option>
                    @endforeach
                    <option value="annual" @selected($isAnnualMode)>
                        Consolidado Anual (100%)
                    </option>
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- 4 Tarjetas KPI Principales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">
                        {{ $isTeacherOnly ? 'Estudiantes Atendidos' : 'Matrícula Activa' }}
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $kpis['total_students'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        En {{ $kpis['total_subjects'] }} {{ $kpis['total_subjects'] === 1 ? 'materia' : 'materias' }} · {{ $kpis['total_sections'] }} secciones
                    </p>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-sky-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">Promedio General</p>
                    <p class="mt-2 text-3xl font-bold {{ $kpis['overall_average'] >= 6 || $kpis['overall_average'] == 0 ? 'text-gray-900' : 'text-rose-700' }}">
                        {{ number_format($kpis['overall_average'], 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $isAnnualMode ? 'Acumulado anual' : ($selectedPeriod?->name ?? 'Período') }} (Mín. 6.00)
                    </p>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 border-emerald-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">Tasa de Aprobación</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($kpis['approval_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-500 mt-1">Registros con nota ≥ 6.00</p>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border-l-4 {{ $kpis['pending_count'] > 0 ? 'border-amber-500' : 'border-emerald-500' }}">
                    <p class="text-xs font-medium text-gray-500 uppercase">Evaluaciones Pendientes</p>
                    <p class="mt-2 text-3xl font-bold {{ $kpis['pending_count'] > 0 ? 'text-amber-600' : 'text-emerald-700' }}">
                        {{ $kpis['pending_count'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        De {{ $kpis['total_assessments'] }} planificadas en el corte
                    </p>
                </div>
            </div>

            <!-- Paneles Operativos -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna Izquierda (2/3): Rendimiento por Materia y Sección -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Rendimiento por Asignatura y Sección</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $isAnnualMode ? 'Consolidado de los 4 períodos' : ($selectedPeriod?->name . ' (' . number_format($selectedPeriod?->weight_percentage ?? 0, 0) . '%)') }}
                            </p>
                        </div>
                        <a href="{{ route('gradebook.index', ['period_id' => $isAnnualMode ? 'annual' : $selectedPeriod?->id]) }}"
                           class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 uppercase">
                            Ir al Cuadro →
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Asignatura / Sección</th>
                                    @unless ($isTeacherOnly)
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Docente</th>
                                    @endunless
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Evaluado</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Promedio</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Aprobados</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($performanceRows as $item)
                                    @php
                                        $subj = $item['subject'];
                                        $sec = $item['section'];
                                        $sum = $item['summary'];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-gray-900 block">{{ $subj->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $subj->gradeLevel->name }} — Sección "{{ $sec->name }}"</span>
                                        </td>

                                        @unless ($isTeacherOnly)
                                            <td class="px-4 py-3 text-gray-600 text-xs">
                                                {{ $subj->teacher?->name ?? 'Sin asignar' }}
                                            </td>
                                        @endunless

                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700">
                                                {{ number_format($sum['planned_weight'], 0) }}%
                                            </span>
                                        </td>

                                        <td class="px-4 py-3 text-center font-bold">
                                            @if ($sum['total_students'] === 0)
                                                <span class="text-xs font-normal text-gray-400 italic">Sin alumnos</span>
                                            @elseif ($sum['planned_weight'] == 0)
                                                <span class="text-xs font-normal text-gray-400 italic">Sin notas</span>
                                            @else
                                                <span class="{{ $sum['section_average'] >= 6 ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ number_format($sum['section_average'], 2) }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-center text-xs text-gray-600">
                                            @if ($sum['total_students'] > 0)
                                                {{ $sum['approved_count'] }} / {{ $sum['total_students'] }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('gradebook.index', ['subject_id' => $subj->id, 'section_id' => $sec->id, 'period_id' => $isAnnualMode ? 'annual' : $selectedPeriod?->id]) }}"
                                               class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs uppercase">
                                                Ver Cuadro
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No hay asignaturas registradas para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Columna Derecha (1/3): Cola de Evaluaciones Pendientes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col justify-between">
                    <div>
                        <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-800">Pendientes de Calificar</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Evaluaciones con notas incompletas</p>
                            </div>
                            <a href="{{ route('assessments.create') }}"
                               class="px-2.5 py-1 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700">
                                + Nueva
                            </a>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @forelse ($pendingAssessments as $pending)
                                <div class="p-4 hover:bg-gray-50 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $pending->title }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $pending->subject->name }} · Sec. "{{ $pending->section->name }}"
                                        </p>
                                        <div class="mt-1 flex items-center gap-2 text-xs">
                                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-medium">
                                                {{ $pending->scores_count }} / {{ $pending->expected_students }} notas
                                            </span>
                                            <span class="text-gray-400">
                                                {{ number_format($pending->weight_percentage, 0) }}%
                                            </span>
                                        </div>
                                    </div>

                                    <a href="{{ route('assessments.scores.edit', $pending) }}"
                                       class="shrink-0 px-3 py-1.5 bg-gray-800 text-white text-xs font-semibold uppercase rounded hover:bg-gray-700">
                                        Calificar
                                    </a>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <p class="text-sm font-medium text-emerald-700">¡Todo al día!</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        No hay evaluaciones con calificaciones pendientes en este corte.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-200 text-center">
                        <a href="{{ route('assessments.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 uppercase">
                            Ver todas las evaluaciones
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>