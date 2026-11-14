<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-tight">
                    Personal Docente y Credenciales
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Registra profesores, administra sus accesos al sistema y habilítalos para carga académica.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800 font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800 font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Formulario de Alta de Docente -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-200/80 p-6">
                <h3 class="text-base font-bold text-slate-900">1. Registrar Nuevo Docente</h3>
                <p class="text-xs text-slate-500 mb-4">Crea las credenciales institucionales de acceso con rol Docente.</p>

                <form method="POST" action="{{ route('teachers.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    @csrf

                    <div class="md:col-span-4">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Nombre Completo
                        </label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                               placeholder="Ej. Prof. Ana María López"
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-3">
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Correo Institucional
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                               placeholder="alopez@edumetrics.sv"
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Contraseña
                        </label>
                        <input id="password" name="password" type="password" required
                               placeholder="Mín. 8 caracteres"
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Confirmar
                        </label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               placeholder="Repite contraseña"
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="md:col-span-1">
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition">
                            + Crear
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado de Docentes -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-200/80 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">2. Plantilla Docente Activa ({{ $teachers->count() }})</h3>
                        <p class="text-xs text-slate-500">Profesores habilitados para iniciar sesión e ingresar notas.</p>
                    </div>

                    <form method="GET" action="{{ route('teachers.index') }}" class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Buscar por nombre o correo..."
                               class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit"
                                class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold uppercase rounded-xl">
                            Buscar
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('teachers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">
                                Limpiar
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3 text-left">Docente</th>
                                <th class="px-6 py-3 text-left">Usuario / Correo</th>
                                <th class="px-6 py-3 text-center">Materias Asignadas</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($teachers as $teacher)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        {{ $teacher->name }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-mono text-xs">
                                        {{ $teacher->email }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($teacher->assigned_subjects_count > 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                                {{ $teacher->assigned_subjects_count }} materia(s)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                                Sin carga asignada
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3 text-xs font-bold uppercase">
                                        <a href="{{ route('teachers.edit', $teacher) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Editar / Clave
                                        </a>

                                        @if ($teacher->assigned_subjects_count === 0)
                                            <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" class="inline"
                                                  onsubmit="return confirm('¿Eliminar permanentemente la cuenta docente de {{ $teacher->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 uppercase">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                        No se encontraron docentes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>