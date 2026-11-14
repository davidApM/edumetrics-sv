<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Editar Asignatura y Carga Docente') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">{{ $subject->name }} ({{ $subject->code }})</p>
            </div>
            <a href="{{ route('academic.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Volver a Gestión Académica
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <form method="POST" action="{{ route('academic.subjects.update', $subject) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code', $subject->code) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm font-mono uppercase">
                            @error('code')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nombre de la Asignatura</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $subject->name) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nivel / Grado</label>
                        <select name="grade_level_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @foreach ($gradeLevels as $level)
                                <option value="{{ $level->id }}"
                                    @selected((string) old('grade_level_id', $subject->grade_level_id) === (string) $level->id)>
                                    {{ $level->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Docente Titular Asignado</label>
                        <select name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Sin asignar</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    @selected((string) old('user_id', $subject->user_id) === (string) $teacher->id)>
                                    {{ $teacher->name }} ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <a href="{{ route('academic.index') }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-300">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 text-white text-xs font-semibold uppercase rounded-md hover:bg-indigo-700">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>