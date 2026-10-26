@extends('layouts.app')
@php
    $titulo = $model->carrera->nombre.' · '.$model->semestre->nombre.' · '.$model->grupoLetra->letra_key;
    $hayFiltros = collect($filtros)->contains(fn ($valor) => $valor !== '');
@endphp
@section('title', $titulo)
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url(auth()->user()->hasRole('admin') ? '/admin/groups' : '/select-grupo/index') }}">Grupos</a></li>
    <li class="breadcrumb-item active" aria-current="page">Grupo {{ $model->grupoLetra->letra_key }}</li>
@endsection
@section('content')
<div class="ui-resource-page grupo-master-view">
    <x-ui.page-header eyebrow="Gestión tutorial" :title="'Grupo '.$model->grupoLetra->letra_key" :description="$model->carrera->nombre.' · '.$model->semestre->nombre">
        <a class="btn btn-outline-primary" href="{{ url(auth()->user()->hasRole('admin') ? '/admin/groups' : '/select-grupo/index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a grupos</a>
        @if(auth()->user()->hasRole('admin'))
            <a class="btn btn-primary" href="{{ route('alumno.importar', ['id_grupo' => $model->id]) }}"><i class="fas fa-file-excel" aria-hidden="true"></i> Importar alumnos</a>
        @endif
    </x-ui.page-header>
    <dl class="ui-summary-grid">
        <div class="ui-summary-item"><dt>Periodo escolar</dt><dd>{{ $model->periodo->nombre }}</dd></div>
        <div class="ui-summary-item"><dt>Tutor del grupo</dt><dd>{{ $model->tutor ? $model->tutor->nombre.' '.$model->tutor->apellido : 'Sin asignar' }}</dd></div>
        <div class="ui-summary-item"><dt>Semestre y grupo</dt><dd>{{ $model->semestre->nombre }} · {{ $model->grupoLetra->letra_key }}</dd></div>
    </dl>
    @if ($model->id_tutor == null)
        <div class="alert alert-warning" role="alert"><strong>Este grupo aún no tiene tutor.</strong> Asigna un tutor desde la administración de grupos.</div>
    @endif
    @if (session('err'))
        <div class="alert alert-danger" role="alert">{{ session('err') }}</div>
    @endif
    <div class="ui-group-toolbar">
        <div><h2>Alumnos del grupo</h2><p>Consulta sus datos y accede al expediente individual.</p></div>
        <div class="ui-form-actions">
            <a class="btn btn-outline-primary" href="{{ url('/diagnostico/index') }}">Diagnósticos</a>
            <a class="btn btn-outline-primary" href="{{ url('/canalizacion/index') }}">Canalizaciones</a>
            @if(auth()->user()->hasRole('admin'))
                <a class="btn btn-primary" href="{{ url('/alumno/create/'.$model->id) }}"><i class="fas fa-user-plus" aria-hidden="true"></i> Añadir alumno</a>
            @endif
        </div>
    </div>
    <form method="get" action="{{ url('/grupo-master/index') }}" class="card ui-form-card mb-4" role="search" aria-label="Filtrar alumnos del grupo">
        <div class="card-body">
            <div class="form-row">
                @foreach(['matricula' => 'Matrícula', 'nombres' => 'Nombres', 'apellidop' => 'Apellido paterno', 'apellidom' => 'Apellido materno'] as $campo => $label)
                    <div class="form-group col-md-3"><label for="grupo-{{ $campo }}">{{ $label }}</label><input id="grupo-{{ $campo }}" type="text" class="form-control" name="{{ $campo }}" value="{{ $filtros[$campo] }}" placeholder="Buscar…"></div>
                @endforeach
            </div>
            <div class="ui-group-filter-bottom">
                <div class="form-group mb-0"><label for="grupo-genero">Género</label><select id="grupo-genero" class="form-control" name="genero"><option value="">Todos</option><option value="0" @selected($filtros['genero'] === '0')>Masculino</option><option value="1" @selected($filtros['genero'] === '1')>Femenino</option></select></div>
                <div class="ui-form-actions">
                    @if($hayFiltros)<a href="{{ url('/grupo-master/index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>@endif
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
                </div>
            </div>
        </div>
    </form>
    <x-ui.list-panel title="Directorio del grupo" :paginator="$alumnos">
        <table class="table ui-table" id="frontend-grid-alumnos">
            <thead><tr><th scope="col">#</th><th scope="col">Alumno</th><th scope="col">Matrícula</th><th scope="col">Género</th><th scope="col" class="text-right">Acciones</th></tr></thead>
            <tbody>
                @forelse($alumnos as $alumno)
                    <tr>
                        <td>{{ $alumnos->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $alumno->nombre_completo }}</strong><small class="d-block text-muted">ID {{ $alumno->id }}</small></td>
                        <td><span class="ui-count">{{ $alumno->matricula }}</span></td><td>{{ $alumno->genero != 0 ? 'Femenino' : 'Masculino' }}</td>
                        <td><div class="ui-inline-actions">
                            <a href="{{ url('/alumno/view/'.$alumno->id) }}" class="btn btn-outline-primary"><i class="fas fa-eye" aria-hidden="true"></i> Ver alumno</a>
                            <a href="{{ url('/alumno/ficha/'.$alumno->id) }}" class="btn btn-outline-primary"><i class="fas fa-file-alt" aria-hidden="true"></i> Expediente</a>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ui-empty-state"><strong>{{ $hayFiltros ? 'No se encontraron alumnos' : 'Aún no hay alumnos asignados a este grupo.' }}</strong>{{ $hayFiltros ? 'Prueba con otros datos o limpia los filtros para volver al listado.' : 'Los alumnos asignados aparecerán en este directorio.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.list-panel>
</div>
@endsection
