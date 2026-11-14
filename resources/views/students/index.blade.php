<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Directorio y Matrícula de Estudiantes') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $canManage ? 'Gestión de matrícula institucional, asignación de secciones y estados.' : 'Nómina de estudiantes asignados a tus secciones.' }}
                </p>
            </div>

            @if ($canManage)
                <a href="{{ route('students.create', ['section_id' => request('section_id')]) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    + Matricular Estudiante
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Resumen rápido de matrícula -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">Total Registros (Filtro)</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-emerald-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">Matrícula Activa</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['active'] }}</p>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-gray-400">
                    <p class="text-xs font-medium text-gray-500 uppercase">Inactivos / Retirados</p>
                    <p class="mt-1 text-2xl font-bold text-gray-600">{{ $stats['inactive'] }}</p>
                </div>
            </div>

            <!-- Barra de Búsqueda y Filtros -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Buscar por NIE o Nombre</label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Ej. 20260101 o Sofía..."
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Grado / Sección</label>
                        <select name="section_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todas las secciones</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>
                                    {{ $section->gradeLevel->name }} — Sección "{{ $section->name }}"
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estado de Matrícula</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="active" @selected(request('status', 'active') === 'active')>Solo Activos</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Solo Inactivos</option>
                            <option value="all" @selected(request('status') === 'all')>Todos</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-xs font-semibold uppercase rounded-md hover:bg-gray-700">
                            Filtrar
                        </button>
                        <a href="{{ route('students.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold uppercase rounded-md hover:bg-gray-300">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de Estudiantes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-12">#</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase w-32">NIE</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Apellidos y Nombres</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Grado / Sección</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($students as $index => $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ ($students->currentPage() - 1) * $students->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                        {{ $student->nie }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ $student->full_name }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $student->section->gradeLevel->name }} — <strong>Sección "{{ $student->section->name }}"</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($student->is_active)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                Activo
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-3">
                                        <a href="{{ route('gradebook.export.student', $student) }}"
                                           target="_blank"
                                           class="text-rose-600 hover:text-rose-800 font-semibold text-xs uppercase">
                                            Boleta PDF
                                        </a>

                                        @if ($canManage)
                                            <a href="{{ route('students.edit', $student) }}"
                                               class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs uppercase">
                                                Editar
                                            </a>

                                            <form method="POST" action="{{ route('students.toggle-status', $student) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="{{ $student->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800' }} font-semibold text-xs uppercase">
                                                    {{ $student->is_active ? 'Inactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No se encontraron estudiantes con los criterios seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>