<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Programar Nueva Evaluación') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('assessments.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="subject_id" class="block text-sm font-medium text-gray-700">Materia</label>
                        <select id="subject_id" name="subject_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Selecciona una materia...</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                    {{ $subject->code }} - {{ $subject->name }} ({{ $subject->gradeLevel->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="section_id" class="block text-sm font-medium text-gray-700">Sección</label>
                            <select id="section_id" name="section_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecciona una sección...</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" @selected(old('section_id') == $section->id)>
                                        {{ $section->gradeLevel->name }} — Sección "{{ $section->name }}"
                                    </option>
                                @endforeach
                            </select>
                            @error('section_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="period_id" class="block text-sm font-medium text-gray-700">Período Evaluativo</label>
                            <select id="period_id" name="period_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Selecciona un período...</option>
                                @foreach ($periods as $period)
                                    <option value="{{ $period->id }}" @selected(old('period_id') == $period->id)>
                                        {{ $period->name }} ({{ $period->weight_percentage }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('period_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Título de la Evaluación</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required
                               placeholder="Ej: Actividad Integradora 1, Laboratorio Práctico, Examen Parcial"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="weight_percentage" class="block text-sm font-medium text-gray-700">Ponderación (%)</label>
                            <input id="weight_percentage" name="weight_percentage" type="number" step="0.01" min="1" max="100"
                                   value="{{ old('weight_percentage', '35.00') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('weight_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Fecha de Evaluación (Opcional)</label>
                            <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <a href="{{ route('assessments.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-indigo-700">
                            Guardar Evaluación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>