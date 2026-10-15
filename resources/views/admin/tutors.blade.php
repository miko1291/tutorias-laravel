@extends('layouts.app')
@section('title', 'Tutores')
@section('breadcrumbs')
    <li class="breadcrumb-item active">Tutores</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Administración académica" title="Tutores" description="Relaciona tutores con sus cuentas y grupos asignados.">
        <a class="btn btn-primary" href="{{ url('/admin/tutors') }}"><i class="fas fa-user-plus"></i> Nuevo tutor</a>
    </x-ui.page-header>
    <form method="POST" action="{{ url($editTutor ? '/admin/tutors/'.$editTutor->id.'/save' : '/admin/tutors/save') }}" class="card ui-form-card mb-4">@csrf<div class="card-body"><div class="ui-form-card__title"><i class="fas fa-chalkboard-teacher"></i> {{ $editTutor ? 'Editar tutor' : 'Registrar tutor' }}</div>
        <div class="form-row">@foreach(['nombre'=>'Nombre','apellido'=>'Apellido'] as $field=>$label)<div class="form-group col-md-3"><label>{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ $editTutor->$field ?? '' }}" required></div>@endforeach
            <div class="form-group col-md-3"><label>Correo</label><input class="form-control" name="correo" type="email" value="{{ $editTutor->correo ?? '' }}" required></div><div class="form-group col-md-3"><label>Teléfono</label><input class="form-control" name="telefono" value="{{ $editTutor->telefono ?? '' }}" required></div>
            <div class="form-group col-md-2"><label>Género</label><select class="form-control" name="genero"><option value="0">Masculino</option><option value="1">Femenino</option></select></div><div class="form-group col-md-4"><label>Cuenta de acceso</label><select class="form-control" name="id_user" required>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) ($editTutor->id_user ?? 0) === (int) $user->id)>{{ $user->username }}</option>@endforeach</select></div>
        </div><button class="btn btn-primary"><i class="fas fa-save"></i> {{ $editTutor ? 'Guardar cambios' : 'Registrar tutor' }}</button>
    </div></form>
    <div class="card ui-table-card"><div class="table-responsive"><table class="table ui-table mb-0"><thead><tr><th>Tutor</th><th>Contacto</th><th>Cuenta</th><th>Grupos</th><th class="text-right">Acciones</th></tr></thead><tbody>@forelse($tutors as $tutor)<tr><td><strong>{{ $tutor->nombre_completo }}</strong></td><td>{{ $tutor->correo }}<small class="d-block text-muted">{{ $tutor->telefono }}</small></td><td>{{ $tutor->user?->username ?? 'Sin cuenta' }}</td><td><span class="badge badge-light">{{ $tutor->grupo_masters_count }}</span></td><td class="text-right"><x-ui.action-menu><a class="dropdown-item" href="{{ url('/admin/tutors?edit='.$tutor->id) }}"><i class="fas fa-edit"></i> Editar</a><form method="POST" action="{{ url('/admin/tutors/'.$tutor->id.'/delete') }}" onsubmit="return confirm('¿Eliminar este tutor?');">@csrf<button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Eliminar</button></form></x-ui.action-menu></td></tr>@empty<tr><td colspan="5" class="ui-empty-state">No hay tutores registrados.</td></tr>@endforelse</tbody></table></div><div class="card-body">{{ $tutors->links() }}</div></div>
</div>
@endsection
