@extends('layouts.app')

@section('title', 'Grupos')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Grupos</li>
@endsection

@section('content')
<div class="admin-resource">
    <div class="admin-resource__heading">
        <div><span>Administración académica</span><h1>Grupos maestros</h1><p>Crea grupos y asigna tutores y alumnos.</p></div>
        @if($editGroup)<a class="btn btn-outline-secondary" href="{{ url('/admin/groups') }}">Cerrar edición</a>@endif
    </div>

    <div class="admin-resource__layout">
        <section class="card admin-resource__form">
            <div class="card-header"><strong>{{ $editGroup ? 'Editar grupo' : 'Crear grupo' }}</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ url($editGroup ? '/admin/groups/'.$editGroup->id.'/save' : '/admin/groups/save') }}">
                    @csrf
                    <div class="form-row">
                        @foreach([
                            'id_periodo' => ['label' => 'Periodo escolar', 'items' => $periodos],
                            'id_carrera' => ['label' => 'Carrera', 'items' => $carreras],
                            'id_semestre' => ['label' => 'Semestre', 'items' => $semestres],
                            'id_grupoLetra' => ['label' => 'Letra', 'items' => $letras],
                            'id_tutor' => ['label' => 'Tutor', 'items' => $tutores],
                        ] as $field => $definition)
                            <div class="form-group col-md-6">
                                <label for="{{ $field }}">{{ $definition['label'] }}</label>
                                <select id="{{ $field }}" class="form-control" name="{{ $field }}" {{ $field !== 'id_tutor' ? 'required' : '' }}>
                                    <option value="">{{ $field === 'id_tutor' ? 'Sin tutor asignado' : 'Selecciona una opción' }}</option>
                                    @foreach($definition['items'] as $item)
                                        @php
                                            $label = $item->nombre ?? $item->letra_key;
                                            if ($field === 'id_tutor') $label = $item->nombre_completo;
                                        @endphp
                                        <option value="{{ $item->id }}" @selected((int) old($field, data_get($editGroup, $field)) === (int) $item->id)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error($field)<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> {{ $editGroup ? 'Guardar cambios' : 'Crear grupo' }}</button>
                </form>
            </div>
        </section>

        @if($editGroup)
        <section class="card admin-resource__form">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Alumnos del grupo</strong><span class="badge badge-primary">{{ $groupStudents->count() }}</span>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('alumno.importar', ['id_grupo' => $editGroup->id]) }}">Importar Excel</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/admin/groups/'.$editGroup->id.'/assign-student') }}" class="form-inline mb-3">
                    @csrf
                    <label class="sr-only" for="id_alumno">Alumno</label>
                    <select class="form-control mr-2 flex-grow-1" id="id_alumno" name="id_alumno" required>
                        <option value="">Selecciona un alumno sin grupo</option>
                        @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->matricula }} · {{ $student->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-success mt-2 mt-md-0"><i class="fas fa-user-plus"></i> Asignar</button>
                </form>
                @if($availableStudents->isEmpty())<p class="small text-muted">No hay alumnos sin grupo disponibles.</p>@endif
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Matrícula</th><th>Alumno</th><th class="text-right">Acción</th></tr></thead>
                        <tbody>
                        @forelse($groupStudents as $student)
                            <tr>
                                <td>{{ $student->matricula }}</td><td>{{ $student->nombre_completo }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ url('/admin/groups/'.$editGroup->id.'/students/'.$student->id.'/unassign') }}" class="d-inline" onsubmit="return confirm('¿Retirar este alumno del grupo?');">
                                        @csrf<button class="btn btn-sm btn-outline-danger"><i class="fas fa-user-minus"></i> Retirar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Este grupo aún no tiene alumnos.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        @endif
    </div>

    <section class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Grupo</th><th>Periodo</th><th>Tutor</th><th>Alumnos</th><th class="text-right">Acciones</th></tr></thead>
                <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td><strong>{{ $group->nombre ?: 'Grupo '.$group->id }}</strong><small class="d-block text-muted">ID {{ $group->id }}</small></td>
                        <td>{{ $group->periodo?->nombre }}</td>
                        <td>{{ $group->tutor?->nombre_completo ?? 'Sin asignar' }}</td>
                        <td><span class="badge badge-light">{{ $group->alumnos_count }}</span></td>
                        <td class="text-right text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ url('/admin/groups?edit='.$group->id) }}"><i class="fas fa-edit"></i> Editar</a>
                            <form method="POST" action="{{ url('/admin/groups/'.$group->id.'/delete') }}" class="d-inline" onsubmit="return confirm('¿Eliminar este grupo? Los alumnos conservarán sus datos.');">@csrf<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay grupos registrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $groups->withQueryString()->links() }}</div>
    </section>
</div>
@endsection

@push('styles')
<style>
.admin-resource{max-width:1250px;margin:12px auto 45px}.admin-resource__heading{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:20px}.admin-resource__heading span{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6c7b8c}.admin-resource__heading h1{margin:5px 0;color:var(--tecm-blue);font-size:2rem}.admin-resource__heading p{margin:0;color:#718092}.admin-resource__layout{display:grid;grid-template-columns:minmax(360px,.9fr) minmax(480px,1.1fr);gap:18px;margin-bottom:20px}.admin-resource .card{border-color:#e1e8f0}.admin-resource .card-header{background:#f7fafd;color:var(--tecm-blue);border-bottom-color:#e1e8f0}.admin-resource label{font-weight:700;color:#355672}.admin-resource .table th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#6c7b8c}.admin-resource .table td{vertical-align:middle}@media(max-width:900px){.admin-resource__layout{grid-template-columns:1fr}}@media(max-width:600px){.admin-resource__heading{display:block}.admin-resource__heading .btn{margin-top:12px}.admin-resource__form .form-inline{display:block}.admin-resource__form .form-inline select{width:100%;margin-right:0!important}}
</style>
@endpush
