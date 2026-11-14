<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Cuadro de Notas y Promedios') }}
                </h2>
                @if ($selectedSubject && $selectedSection)
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $selectedSubject->name }} ({{ $selectedSubject->gradeLevel->name }}) — Sección "{{ $selectedSection->name }}"
                    </p>
                @endif
            </div>

            @if ($selectedSubject && $selectedSection)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('gradebook.export.section', [
                        'subject_id' => $selectedSubject->id,
                        'section_id' => $selectedSection->id,
                        'period_id' => $isAnnualMode ? 'annual' : $selectedPeriod?->id,
                    ]) }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700 transition">
                        Exportar Acta PDF
                    </a>

                    <a href="{{ route('assessments.index', ['subject_id' => $selectedSubject->id, 'period_id' => $selectedPeriod?->id]) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Gestionar Evaluaciones
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
            subjectId: '{{ $selectedSubject?->id }}',
            sectionId: '{{ $selectedSection?->id }}',
            subjects: {{ $subjects->map(fn($s) => ['id' => (string) $s->id, 'grade_level_id' => $s->grade_level_id])->values()->toJson() }},
            sections: {{ $sections->map(fn($sec) => ['id' => (string) $sec->id, 'name' => $sec->name, 'grade_level_id' => $sec->grade_level_id])->values()->toJson() }},
            get filteredSections() {
                const subj = this.subjects.find(s => s.id === String(this.subjectId));
                if (!subj) return [];
                return this.sections.filter(sec => sec.grade_level_id === subj.grade_level_id);
            },
            onSubjectChange() {
                const valid = this.filteredSections;
                if (!valid.some(sec => sec.id === String(this.sectionId))) {
                    this.sectionId = valid.length ? valid[0].id : '';
                }
            }
         }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtros de Consulta -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" action="{{ route('gradebook.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Materia</label>
                        <select name="subject_id"
                                x-model="subjectId"
                                @change="onSubjectChange()"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">
                                    {{ $subject->name }} ({{ $subject->gradeLevel->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sección</label>
                        <select name="section_id"
                                x-model="sectionId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <template x-for="sec in filteredSections" :key="sec.id">
                                <option :value="sec.id" :selected="sec.id === String(sectionId)" x-text="'Sección &quot;' + sec.name + '&quot;'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vista / Período</label>
                        <select name="period_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected(! $isAnnualMode && $selectedPeriod?->id === $period->id)>
                                    {{ $period->name }} ({{ number_format($period->weight_percentage, 0) }}%)
                                </option>
                            @endforeach
                            <option value="annual" @selected($isAnnualMode)>
                                Resumen Anual Consolidado (100%)
                            </option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                            Consultar Cuadro
                        </button>
                    </div>
                </form>
            </div>

            @if ($matrix)
                @php
                    $summary = $matrix['summary'];
                @endphp

                <!-- Tarjetas de Indicadores -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                        <p class="text-xs font-medium text-gray-500 uppercase">Ponderación Planificada</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($summary['planned_weight'], 2) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">De 100.00% del {{ $isAnnualMode ? 'año lectivo' : 'período' }}</p>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-sky-500">
                        <p class="text-xs font-medium text-gray-500 uppercase">Promedio de Sección</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($summary['section_average'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Nota mínima aprobación: 6.00</p>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-500">
                        <p class="text-xs font-medium text-gray-500 uppercase">Aprobados (≥ 6.00)</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-700">
                            {{ $summary['approved_count'] }} <span class="text-sm font-normal text-gray-500">/ {{ $summary['total_students'] }}</span>
                        </p>
                        <p class="text-xs text-emerald-600 mt-1">{{ $summary['approval_rate'] }}% de aprobación</p>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-rose-500">
                        <p class="text-xs font-medium text-gray-500 uppercase">Reprobados (&lt; 6.00)</p>
                        <p class="mt-1 text-2xl font-bold text-rose-700">{{ $summary['failed_count'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Estudiantes bajo mínimo</p>
                    </div>
                </div>

                <!-- Matriz de Calificaciones -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        @if ($matrix['mode'] === 'period')
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-10">#</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-28">NIE</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Estudiante</th>

                                        @foreach ($matrix['assessments'] as $assessment)
                                            <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">
                                                <a href="{{ route('assessments.scores.edit', $assessment) }}"
                                                   title="Editar calificaciones de {{ $assessment->title }}"
                                                   class="text-indigo-600 hover:underline block">
                                                    {{ $assessment->title }}
                                                </a>
                                                <span class="block text-xs font-normal text-gray-400">
                                                    ({{ number_format($assessment->weight_percentage, 2) }}%)
                                                </span>
                                            </th>
                                        @endforeach

                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase bg-gray-100">
                                            Nota Período
                                        </th>
                                        @if ($summary['planned_weight'] > 0 && $summary['planned_weight'] < 100)
                                            <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">
                                                Proyección
                                            </th>
                                        @endif
                                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">
                                            Aporte ({{ number_format($selectedPeriod->weight_percentage, 0) }}%)
                                        </th>
                                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($matrix['rows'] as $index => $row)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $row['student']->nie }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ $row['student']->full_name }}
                                                <a href="{{ route('gradebook.export.student', $row['student']) }}"
                                                   target="_blank"
                                                   class="ml-2 inline-block text-[11px] font-semibold text-rose-600 hover:underline">
                                                    [Boleta PDF]
                                                </a>
                                            </td>

                                            @foreach ($matrix['assessments'] as $assessment)
                                                @php
                                                    $cell = $row['scores'][$assessment->id] ?? ['raw' => null, 'weighted' => null];
                                                @endphp
                                                <td class="px-4 py-3 text-center">
                                                    @if ($cell['raw'] !== null)
                                                        <span class="font-semibold {{ $cell['raw'] >= 6 ? 'text-gray-900' : 'text-rose-600' }}">
                                                            {{ number_format($cell['raw'], 2) }}
                                                        </span>
                                                        <span class="block text-[11px] text-gray-400">
                                                            +{{ number_format($cell['weighted'], 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400 italic">S/N</span>
                                                    @endif
                                                </td>
                                            @endforeach

                                            <td class="px-4 py-3 text-center font-bold bg-gray-50 {{ $row['passed'] ? 'text-emerald-700' : 'text-rose-700' }}">
                                                {{ number_format($row['final_grade'], 2) }}
                                            </td>

                                            @if ($summary['planned_weight'] > 0 && $summary['planned_weight'] < 100)
                                                <td class="px-4 py-3 text-center text-gray-600">
                                                    {{ $row['projected_grade'] !== null ? number_format($row['projected_grade'], 2) : '—' }}
                                                </td>
                                            @endif

                                            <td class="px-4 py-3 text-center text-gray-600">
                                                {{ number_format($row['annual_contribution'], 2) }}
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                @if ($row['passed'])
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                        Aprobado
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                                        Reprobado
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                                                No hay estudiantes activos en esta sección.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @else
                            <!-- Tabla de Resumen Anual Consolidado -->
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-10">#</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-28">NIE</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Estudiante</th>

                                        @foreach ($matrix['periods'] as $period)
                                            <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">
                                                {{ $period->name }}
                                                <span class="block text-xs font-normal text-gray-400">
                                                    ({{ number_format($period->weight_percentage, 0) }}%)
                                                </span>
                                            </th>
                                        @endforeach

                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase bg-gray-100">
                                            Promedio Final
                                        </th>
                                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($matrix['rows'] as $index => $row)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $row['student']->nie }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ $row['student']->full_name }}
                                                <a href="{{ route('gradebook.export.student', $row['student']) }}"
                                                   target="_blank"
                                                   class="ml-2 inline-block text-[11px] font-semibold text-rose-600 hover:underline">
                                                    [Boleta PDF]
                                                </a>
                                            </td>

                                            @foreach ($matrix['periods'] as $period)
                                                @php
                                                    $pData = $row['periods'][$period->id] ?? ['grade' => 0, 'contribution' => 0];
                                                @endphp
                                                <td class="px-4 py-3 text-center">
                                                    <span class="font-semibold {{ $pData['grade'] >= 6 ? 'text-gray-900' : 'text-rose-600' }}">
                                                        {{ number_format($pData['grade'], 2) }}
                                                    </span>
                                                    <span class="block text-[11px] text-gray-400">
                                                        +{{ number_format($pData['contribution'], 2) }}
                                                    </span>
                                                </td>
                                            @endforeach

                                            <td class="px-4 py-3 text-center font-bold bg-gray-50 {{ $row['passed'] ? 'text-emerald-700' : 'text-rose-700' }}">
                                                {{ number_format($row['final_grade'], 2) }}
                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                @if ($row['passed'])
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                        Aprobado
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                                        Reprobado
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                                No hay estudiantes activos en esta sección.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white p-8 rounded-lg shadow-sm text-center text-gray-500">
                    No tienes materias o secciones asignadas para generar un cuadro de notas.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>