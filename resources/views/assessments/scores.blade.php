<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Registro de Calificaciones
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $assessment->title }} · {{ $assessment->subject->name }} ({{ $assessment->subject->gradeLevel->name }}) — Sección "{{ $assessment->section->name }}"
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                {{ $assessment->period->name }} · Ponderación: {{ number_format($assessment->weight_percentage, 2) }}%
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->has('scores'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm text-sm">
                    {{ $errors->first('scores') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('assessments.scores.update', $assessment) }}">
                    @csrf
                    @method('PUT')

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-12">#</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-32">NIE</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Estudiante</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase w-36">Nota (0 - 10)</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($students as $index => $student)
                                    @php
                                        $record = $existingScores->get($student->id);
                                        $currentScore = old("scores.{$student->id}", $record?->score);
                                        $currentObs = old("observations.{$student->id}", $record?->observations);
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $student->nie }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $student->full_name }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number"
                                                   name="scores[{{ $student->id }}]"
                                                   value="{{ $currentScore }}"
                                                   step="0.01"
                                                   min="0"
                                                   max="10"
                                                   placeholder="0.00"
                                                   class="w-28 text-center rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error("scores.{$student->id}")
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   name="observations[{{ $student->id }}]"
                                                   value="{{ $currentObs }}"
                                                   maxlength="255"
                                                   placeholder="Opcional (ej. Entrega tardía, recuperación...)"
                                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @error("observations.{$student->id}")
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            No hay estudiantes activos matriculados en esta sección.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            Total estudiantes activos: <strong>{{ $students->count() }}</strong>
                        </span>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('assessments.index') }}"
                               class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-300">
                                Volver
                            </a>
                            @if ($students->isNotEmpty())
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-indigo-700">
                                    Guardar Calificaciones
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>