@extends('layouts.app')

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $alumnos */
    $filtros = [
        'id' => request()->query('id'),
        'nombres' => request()->query('nombres'),
        'apellidop' => request()->query('apellidop'),
        'apellidom' => request()->query('apellidom'),
        'matricula' => request()->query('matricula'),
    ];
    $serialBase = $alumnos->firstItem() ? $alumnos->firstItem() - 1 : 0;
@endphp

@section('title', 'Alumnos')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Alumnos</li>
@endsection

@section('content')
<div class="alumno-index">

    <h1>Alumnos</h1>

    <p>
        <a class="btn btn-success" href="{{ url('/alumno/create') }}"><i class="fas fa-user-plus"></i> Nuevo alumno</a>
    </p>

    <div class="table-responsive border rounded">
        <form method="get" action="{{ url('/alumno/index') }}" id="alumno-grid">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Id</th>
                        <th>Nombres</th>
                        <th>Apellido paterno</th>
                        <th>Apellido materno</th>
                        <th>Matrícula</th>
                        <th class="text-center"></th>
                    </tr>
                    <tr class="filters">
                        <td></td>
                        <td><input type="text" class="form-control form-control-sm" name="id" value="{{ $filtros['id'] }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="nombres" value="{{ $filtros['nombres'] }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="apellidop" value="{{ $filtros['apellidop'] }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="apellidom" value="{{ $filtros['apellidom'] }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="matricula" value="{{ $filtros['matricula'] }}"></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td class="text-center">{{ $serialBase + $loop->index + 1 }}</td>
                            <td>{{ $alumno->id }}</td>
                            <td>{{ $alumno->nombres }}</td>
                            <td>{{ $alumno->apellidop }}</td>
                            <td>{{ $alumno->apellidom }}</td>
                            <td>{{ $alumno->matricula }}</td>
                            <td class="text-center text-nowrap">
                                <a href="{{ url('/alumno/view/' . $alumno->id) }}" title="Ver alumno" aria-label="Ver alumno" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                                <a href="{{ url('/alumno/update/' . $alumno->id) }}" title="Editar alumno" aria-label="Editar alumno" class="btn btn-sm btn-outline-primary"><i class="fas fa-pencil-alt"></i></a>
                                <form method="post" action="{{ url('/alumno/delete/' . $alumno->id) }}" class="d-inline js-form-confirm" data-confirm="¿Deseas eliminar este alumno?">
                                    @csrf
                                    <button type="submit" title="Eliminar alumno" aria-label="Eliminar alumno" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron resultados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    <div class="mt-3">
        {{ $alumnos->withQueryString()->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).on('click', '.js-form-confirm button[type="submit"]', function (e) {
    if (!window.confirm($(this).closest('form').data('confirm'))) {
        e.preventDefault();
    }
});
</script>
@endpush
