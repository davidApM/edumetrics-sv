<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Matricular Nuevo Estudiante') }}
            </h2>
            <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Volver al directorio
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <form method="POST" action="{{ route('students.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Número de Identificación Estudiantil (NIE)</label>
                        <input type="text"
                               name="nie"
                               value="{{ old('nie') }}"
                               placeholder="Ej. 20260201"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm font-mono">
                        @error('nie')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombres</label>
                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   placeholder="Ej. Carlos Alberto"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('first_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Apellidos</label>
                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   placeholder="Ej. Martínez López"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('last_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Grado y Sección Asignada</label>
                        <select name="section_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Seleccione una sección...</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}"
                                    @selected((string) old('section_id', $defaultSectionId) === (string) $section->id)>
                                    {{ $section->gradeLevel->name }} — Sección "{{ $section->name }}"
                                </option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               @checked(old('is_active', true))
                               class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <label for="is_active" class="text-sm text-gray-700">
                            Matrícula activa (incluir en listados de calificación y cuadros de notas)
                        </label>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <a href="{{ route('students.index') }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-indigo-700">
                            Guardar Matrícula
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>