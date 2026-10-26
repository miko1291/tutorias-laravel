@extends('layouts.app')

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $alumnos */
    $campos = ['id' => 'ID', 'nombres' => 'Nombres', 'apellidop' => 'Apellido paterno', 'apellidom' => 'Apellido materno', 'matricula' => 'Matrícula'];
    $hayFiltros = collect(array_keys($campos))->contains(fn ($campo) => request()->filled($campo));
    $contexto = request()->only(['id_grupo', 'genero', 'fecha_nac', 'correo', 'telefono', 'ciudad']);
@endphp

@section('title', 'Alumnos')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Alumnos</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumnos.css') }}?v={{ filemtime(public_path('css/alumnos.css')) }}">
@endpush

@section('content')
<div class="alumno-index">
    <x-ui.page-header eyebrow="Gestión de alumnos" title="Alumnos" description="Listado de alumnos registrados.">
        <a class="btn btn-outline-primary" href="{{ route('alumno.importar') }}"><i class="fas fa-file-excel" aria-hidden="true"></i> Importar Excel</a>
        <a class="btn btn-primary" href="{{ url('/alumno/create') }}"><i class="fas fa-user-plus" aria-hidden="true"></i> Nuevo alumno</a>
    </x-ui.page-header>

    <section class="alumnos-panel" aria-labelledby="alumnos-listado">
        <div class="alumnos-panel__heading">
            <div class="alumnos-panel__title">
                <span class="alumnos-panel__icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
                <div>
                    <h2 id="alumnos-listado">Directorio de alumnos</h2>
                    <p>{{ number_format($alumnos->total()) }} {{ $alumnos->total() === 1 ? 'alumno encontrado' : 'alumnos encontrados' }}</p>
                </div>
            </div>
            <span class="alumnos-page-info">Página {{ $alumnos->currentPage() }} de {{ $alumnos->lastPage() }}</span>
        </div>

        <form method="get" action="{{ url('/alumno/index') }}" id="alumno-grid" class="alumnos-filters" role="search" aria-label="Filtrar alumnos">
            @foreach ($contexto as $campo => $valor)
                <input type="hidden" name="{{ $campo }}" value="{{ $valor }}">
            @endforeach
            <div class="alumnos-filters__heading"><i class="fas fa-filter" aria-hidden="true"></i> Filtrar alumnos <span>Completa uno o más campos para buscar.</span></div>
            <div class="alumnos-filters__fields">
                @foreach ($campos as $campo => $etiqueta)
                    <div class="alumnos-filter">
                        <label for="filtro-{{ $campo }}">{{ $etiqueta }}</label>
                        <input type="text" class="form-control" id="filtro-{{ $campo }}" name="{{ $campo }}" value="{{ request()->query($campo) }}" placeholder="{{ $campo === 'id' ? 'Ej. 123' : 'Buscar…' }}" @if($campo === 'id') inputmode="numeric" @endif>
                    </div>
                @endforeach
            </div>
            <div class="alumnos-filters__actions">
                @if ($hayFiltros)
                    <a href="{{ url('/alumno/index').($contexto ? '?'.http_build_query($contexto) : '') }}" class="alumnos-clear"><i class="fas fa-times" aria-hidden="true"></i> Limpiar filtros</a>
                @endif
                <button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
            </div>
        </form>

        <div class="table-responsive alumnos-table-wrap">
            <table class="table alumnos-table mb-0">
                <caption class="sr-only">Directorio de alumnos con matrícula y acciones de consulta, edición y eliminación.</caption>
                <thead>
                    <tr>
                        <th scope="col" class="alumnos-number">#</th>
                        <th scope="col">Alumno</th>
                        <th scope="col">Matrícula</th>
                        <th scope="col" class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td class="alumnos-number">{{ $alumnos->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="alumnos-person">
                                    <span class="alumnos-avatar" aria-hidden="true">{{ mb_substr($alumno->nombres, 0, 1) }}{{ mb_substr($alumno->apellidop, 0, 1) }}</span>
                                    <div>
                                        <a class="alumnos-name" href="{{ url('/alumno/view/' . $alumno->id) }}">{{ $alumno->nombres }} {{ $alumno->apellidop }} {{ $alumno->apellidom }}</a>
                                        <span class="alumnos-id">ID: {{ $alumno->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="alumnos-matricula">{{ $alumno->matricula }}</span></td>
                            <td>
                                <div class="alumnos-row-actions">
                                    <a href="{{ url('/alumno/view/' . $alumno->id) }}" title="Ver alumno" aria-label="Ver a {{ $alumno->nombres }}" class="btn alumnos-action"><i class="fas fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ url('/alumno/update/' . $alumno->id) }}" title="Editar alumno" aria-label="Editar a {{ $alumno->nombres }}" class="btn alumnos-action"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>
                                    <form method="post" action="{{ url('/alumno/delete/' . $alumno->id) }}" class="js-form-confirm" data-confirm="¿Deseas eliminar este alumno?">
                                        @csrf
                                        <button type="submit" title="Eliminar alumno" aria-label="Eliminar a {{ $alumno->nombres }}" class="btn alumnos-action alumnos-action--delete"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="alumnos-empty">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <strong>No se encontraron alumnos</strong>
                            <p>{{ $hayFiltros ? 'Prueba con otros datos o limpia los filtros para ver más resultados.' : 'Agrega un nuevo alumno o importa tu listado desde Excel.' }}</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="alumnos-panel__footer">
            <p>Mostrando <strong>{{ $alumnos->firstItem() ?? 0 }}–{{ $alumnos->lastItem() ?? 0 }}</strong> de <strong>{{ number_format($alumnos->total()) }}</strong> alumnos</p>
            @if ($alumnos->hasPages())
                {{ $alumnos->withQueryString()->links() }}
            @endif
        </footer>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.alumno-index .js-form-confirm').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});
</script>
@endpush
