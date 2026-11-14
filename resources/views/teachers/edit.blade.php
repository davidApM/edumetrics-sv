<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-tight">
                    Editar Docente y Restablecer Credenciales
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Actualiza el nombre, correo de acceso o asigna una nueva contraseña para {{ $teacher->name }}.
                </p>
            </div>
            <a href="{{ route('teachers.index') }}" class="text-xs font-bold uppercase text-slate-600 hover:text-slate-900">
                ← Volver a Docentes
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-2xl border border-slate-200/80 p-6">
                <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Nombre Completo del Docente
                        </label>
                        <input id="name" name="name" type="text" value="{{ old('name', $teacher->name) }}" required
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Correo Institucional (Usuario de Acceso)
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email', $teacher->email) }}" required
                               class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-1">
                            Cambio de Contraseña (Opcional)
                        </h4>
                        <p class="text-xs text-slate-500 mb-3">
                            Deja estos campos en blanco si deseas conservar la contraseña actual del docente.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Nueva Contraseña
                                </label>
                                <input id="password" name="password" type="password" placeholder="Mín. 8 caracteres"
                                       class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('password')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Confirmar Contraseña
                                </label>
                                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repite la clave"
                                       class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('teachers.index') }}"
                           class="px-4 py-2 text-xs font-bold uppercase text-slate-600 hover:text-slate-900">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>