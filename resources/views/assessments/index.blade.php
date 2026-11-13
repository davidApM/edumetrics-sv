<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Planificación de Evaluaciones') }}
            </h2>
            <a href="{{ route('assessments.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                + Nueva Evaluación
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" action="{{ route('assessments.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Período</label>
                        <select name="period_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todos los períodos</option>
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>
                                    {{ $period->name }} ({{ $period->weight_percentage }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Materia</label>
                        <select name="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todas las materias</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>
                                    {{ $subject->name }} - {{ $subject->gradeLevel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                            Filtrar
                        </button>
                        <a href="{{ route('assessments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Evaluación</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Materia / Grado</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Sección</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Período</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Ponderación</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Notas</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($assessments as $assessment)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $assessment->title }}
                                        @if ($assessment->due_date)
                                            <span class="block text-xs text-gray-500">Fecha: {{ $assessment->due_date->format('d/m/Y') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $assessment->subject->name }}
                                        <span class="block text-xs text-gray-500">{{ $assessment->subject->gradeLevel->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">Sección "{{ $assessment->section->name }}"</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $assessment->period->name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            {{ number_format($assessment->weight_percentage, 2) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600">
                                        {{ $assessment->scores_count }} registradas
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <form method="POST" action="{{ route('assessments.destroy', $assessment) }}" class="inline"
                                              onsubmit="return confirm('¿Eliminar esta evaluación y sus calificaciones asociadas?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs uppercase">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        Aún no hay evaluaciones registradas. Haz clic en <strong>+ Nueva Evaluación</strong> para comenzar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $assessments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>