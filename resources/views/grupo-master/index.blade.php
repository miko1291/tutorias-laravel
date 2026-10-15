@extends('layouts.app')

@php
    /** @var \App\Models\GrupoMaster $model */
    /** @var \Illuminate\Pagination\LengthAwarePaginator $alumnos */
    /** @var array $filtros */
    $titulo = $model->carrera->nombre . ' - ' . $model->semestre->nombre . ' - ' . $model->grupoLetra->letra_key;
    $serialBase = $alumnos->firstItem() ? $alumnos->firstItem() - 1 : 0;
    $resumen = $alumnos->total() > 0
        ? 'Mostrando <b>' . $alumnos->firstItem() . '</b> - <b>' . $alumnos->lastItem() . '</b> de <b>' . $alumnos->total() . '</b> alumnos'
        : 'Mostrando <b>0</b> de <b>0</b> alumnos';
@endphp

@section('title', $titulo)

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">{{ $titulo }}</li>
@endsection

@section('content')
<div class="grupo-master-view">

    <!-- cell-data-tittle-nowidth -->
    <div class="bg-light pt-2 pb-2 text-center rounded border">
        <h4 class="text-black-50 m-0">{{ $titulo }}</h4>
    </div>

    <hr class="dropdown-divider">

    <div class="table-responsive">
        <table class="table table-sm table-light border table-hover mt-2 gm-detalle">
            <thead class="gm-detalle-head">
                <tr><th colspan="2"><h6 class="panel-title mb-0"><i class="fas fa-users"></i> INFORMACIÓN DEL GRUPO</h6></th></tr>
            </thead>
            <tbody>
                <tr><th class="gm-detalle-label">Periodo</th><td><b>{{ $model->periodo->nombre }}</b></td></tr>
                <tr><th class="gm-detalle-label">Tutor</th><td>@if ($model->id_tutor != null)<span class="text-bold">{{ $model->tutor->nombre }} {{ $model->tutor->apellido }}</span>@else<span class="text-danger font-weight-bold">SIN ASIGNAR</span>@endif</td></tr>
                <tr><th class="gm-detalle-label">Carrera</th><td><b>{{ $model->carrera->nombre }}</b></td></tr>
                <tr><th class="gm-detalle-label">Semestre</th><td><b>{{ $model->semestre->nombre }}</b></td></tr>
                <tr><th class="gm-detalle-label">Grupo</th><td><b>{{ $model->grupoLetra->letra_key }}</b></td></tr>
            </tbody>
        </table>
    </div>

    @if ($model->id_tutor == null)
        <div class="alert alert-danger mt-2" role="alert">
            <b>ESTE GRUPO NO TIENE ASIGNADO NINGÚN TUTOR!.</b> Asigne un tutor para poder ver los botones correspondientes.
        </div>
    @endif

    <hr class="dropdown-divider">

    @if ($alumnos->count() > 0)

        @if (session('err'))
            <div class="alert alert-danger" role="alert">
                <b>{{ session('err') }}</b>
            </div>
        @endif

        <div class="frontend-students-panel">
            <div class="gm-panel-head">
                <h6 class="panel-title mb-0"><i class="fas fa-user-friends"></i> ALUMNOS DE ALTA</h6>
                <div class="gm-toolbar">
                    <a id="create-diagnostic" class="btn-export btn-sm-export btn-action-basics mr-2 btn-disabled" href="{{ url('/diagnostico/create/' . $model->id) }}"><i class="fas fa-external-link-alt"></i> Diagnóstico</a>
                    <a id="create-canalizacion" class="btn-export btn-sm-export btn-action-basics mr-2 btn-disabled" href="{{ url('/canalizacion/create/' . $model->id) }}"><i class="fas fa-external-link-alt"></i> Canalizar</a>
                    <a id="add-alumno" class="btn-export btn-sm-export btn-action-basics mr-2" href="{{ url('/alumno/create/' . $model->id) }}"><i class="bi bi-person-fill-add"></i> Añadir</a>
                </div>
            </div>
            <div class="frontend-students-help"><span><i class="fas fa-info"></i></span><div><strong>Selecciona un alumno</strong><small>Escribe en un filtro y presiona Enter para buscar.</small></div></div>
            <div class="gm-summary">{!! $resumen !!}</div>

            <form id="gm-filtros-form" method="get" action="{{ url('/grupo-master/index') }}">
                <input type="hidden" name="id" value="{{ $model->id }}">
                <div class="table-responsive">
                    <table id="frontend-grid-alumnos" class="table table-sm">
                        <thead>
                            <tr class="cell-data-tittle-nowidth">
                                <th class="text-center" style="font-size:small !important;">#</th>
                                <th class="text-center" style="font-size:small !important;"><input type="checkbox" id="gm-check-all"></th>
                                <th class="text-center" style="font-size:small !important;">Matrícula</th>
                                <th class="text-center" style="font-size:small !important;">Apellido paterno</th>
                                <th class="text-center" style="font-size:small !important;">Apellido materno</th>
                                <th class="text-center" style="font-size:small !important;">Nombres</th>
                                <th class="text-center" style="font-size:small !important;">Genero</th>
                                <th class="text-center" style="font-size:small !important;"></th>
                            </tr>
                            <tr class="filters">
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;"></td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;"></td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;"><input type="text" class="form-control form-control-sm" name="matricula" value="{{ $filtros['matricula'] }}" placeholder="Buscar..." autocomplete="off"></td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;"><input type="text" class="form-control form-control-sm" name="apellidop" value="{{ $filtros['apellidop'] }}" placeholder="Buscar..." autocomplete="off"></td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;"><input type="text" class="form-control form-control-sm" name="apellidom" value="{{ $filtros['apellidom'] }}" placeholder="Buscar..." autocomplete="off"></td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;text-align:center;"><input type="text" class="form-control form-control-sm" name="nombres" value="{{ $filtros['nombres'] }}" placeholder="Buscar..." autocomplete="off"></td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;">
                                    <select class="form-control form-control-sm" name="genero">
                                        <option value="">Todos</option>
                                        <option value="0" @selected((string) $filtros['genero'] === '0')>MASCULINO</option>
                                        <option value="1" @selected((string) $filtros['genero'] === '1')>FEMENINO</option>
                                    </select>
                                </td>
                                <td class="cell-data-tittle-nowidth" style="font-size:small !important;"></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alumnos as $alumno)
                                <tr>
                                    <td>{{ $serialBase + $loop->index + 1 }}</td>
                                    <td><input type="checkbox" name="selection[]" value="{{ $alumno->id }}"></td>
                                    <td class="text-center"><b>{{ $alumno->matricula }}</b></td>
                                    <td class="text-center">{{ $alumno->apellidop }}</td>
                                    <td class="text-center">{{ $alumno->apellidom }}</td>
                                    <td class="text-center">{{ $alumno->nombres }}</td>
                                    <td class="text-center">{{ $alumno->genero != 0 ? 'FEMENINO' : 'MASCULINO' }}</td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ url('/alumno/view/' . $alumno->id . '?id_grupo=' . $model->id) }}" title="Ver alumno" aria-label="Ver alumno" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                                        <a href="{{ url('/alumno/ficha/' . $alumno->id . '?id_grupo=' . $model->id) }}" title="Ver ficha de expediente" aria-label="Ver ficha de expediente" class="btn btn-sm btn-info"><i class="fas fa-file-alt"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="gm-pager">
                {{ $alumnos->withQueryString()->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info text-uppercase" role="alert">
            <b>Aún no hay alumnos asignados a este grupo.</b>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function submitSelectedStudent(url) {
    var selected = $('#frontend-grid-alumnos input[name="selection[]"]:checked').first();
    if (!selected.length) {
        return;
    }
    var form = $('<form>', {method: 'post', action: url});
    form.append($('<input>', {type: 'hidden', name: 'selection[]', value: selected.val()}));
    form.append($('<input>', {
        type: 'hidden',
        name: '_token',
        value: $('meta[name="csrf-token"]').attr('content')
    }));
    $('body').append(form);
    form.trigger('submit');
}

$(document).ready(function() {
    $("#create-diagnostic").click(function(e) {
        e.preventDefault();
        var submitUrl = $(this).attr('href');
        submitSelectedStudent(submitUrl);
    });

    $("#create-canalizacion").click(function(e) {
        e.preventDefault();
        var submitUrl = $(this).attr('href');
        submitSelectedStudent(submitUrl);
    });

    $('#frontend-grid-alumnos').on('change', 'select', function () {
        $('#gm-filtros-form').trigger('submit');
    });

    $("input:checkbox[name='selection[]']").on('click', function() {
        var box = $(this);
        if (box.is(":checked")) {
            $("input:checkbox[name='selection[]']").prop("checked", false);
            $('tr.table-danger').prop('class', 'w1');
            box.prop("checked", true);
            $("#gm-check-all").prop("checked", false);

            $("a#create-diagnostic").prop('class',"btn-export btn-sm-export btn-action-basics mr-2")
            $("a#create-canalizacion").prop('class',"btn-export btn-sm-export btn-action-basics mr-2")
        } else {
            box.prop("checked", false);

            $("a#create-diagnostic").prop('class',"btn-export btn-sm-export btn-action-basics mr-2 btn-disabled")
            $("a#create-canalizacion").prop('class',"btn-export btn-sm-export btn-action-basics mr-2 btn-disabled")
        }
    });

    $("#gm-check-all").on('click', function() {
        if ($(this).is(":checked")) {
            $("input:checkbox[name='selection[]']").prop("checked", true);
            $("a#create-diagnostic").prop('class',"btn-export btn-sm-export btn-action-basics mr-2")
            $("a#create-canalizacion").prop('class',"btn-export btn-sm-export btn-action-basics mr-2")
        } else {
            $("input:checkbox[name='selection[]']").prop("checked", false);
            $("a#create-diagnostic").prop('class',"btn-export btn-sm-export btn-action-basics mr-2 btn-disabled")
            $("a#create-canalizacion").prop('class',"btn-export btn-sm-export btn-action-basics mr-2 btn-disabled")
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.gm-detalle{max-width:760px}.gm-detalle-head th{background:#f8f9fa;font-size:small;padding:.5rem 1.25rem}.gm-detalle-label{text-transform:uppercase;background-color:#e9e9e9;font-size:smaller;font-weight:400;width:170px}
.frontend-students-panel{border:1px solid #e3eaf2;border-radius:6px;background:#fff;box-shadow:0 1px 4px rgba(28,61,93,.05)}
.gm-panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:.5rem 1.25rem;background:#f8f9fa;border-bottom:1px solid #e3eaf2;border-radius:6px 6px 0 0}
.gm-panel-head .panel-title{font-size:small}
.gm-toolbar{display:flex;align-items:center}
.btn-export{display:inline-flex;align-items:center;gap:6px;padding:.3rem .8rem;border:1px solid var(--tecm-blue);border-radius:4px;background:var(--tecm-blue);color:#fff !important;font-size:small;font-weight:700;text-decoration:none}
.btn-export:hover{background:var(--tecm-blue);color:#fff;text-decoration:none}
.btn-export.btn-disabled{opacity:.55;pointer-events:auto}
.gm-summary{padding:.6rem 1.25rem;color:#6c7b8c;font-size:.85rem;border-bottom:1px solid #eef2f6}
.frontend-students-help{display:flex;gap:10px;align-items:flex-start;padding:.7rem 1.25rem;background:#f4f8fc;border-bottom:1px solid #e3eaf2;font-size:.85rem;color:#33506d}
.frontend-students-help i{color:var(--tecm-blue)}
.frontend-students-help div{display:flex;flex-direction:column}
.frontend-students-help small{color:#60758a}
#frontend-grid-alumnos{margin:0}
#frontend-grid-alumnos .filters td{background:#fbfcfe}
#frontend-grid-alumnos td,#frontend-grid-alumnos th{vertical-align:middle}
.gm-pager{padding:.8rem 1.25rem}
.gm-pager .pagination{margin:0}
</style>
@endpush
