@extends('layouts.app')
@section('title', 'Usuarios')
@section('breadcrumbs')
    <li class="breadcrumb-item active">Usuarios y roles</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Administración" title="Usuarios y roles" description="Gestiona las cuentas, estados y permisos del sistema.">
        <a class="btn btn-primary" href="{{ url('/admin/users') }}"><i class="fas fa-user-plus"></i> Nueva cuenta</a>
    </x-ui.page-header>
    <form method="POST" action="{{ url($editUser ? '/admin/users/'.$editUser->id.'/save' : '/admin/users/save') }}" class="card ui-form-card mb-4">@csrf
        <div class="card-body">
            <div class="ui-form-card__title"><i class="fas fa-{{ $editUser ? 'user-edit' : 'user-plus' }}"></i> {{ $editUser ? 'Editar cuenta' : 'Crear cuenta' }}</div>
            <div class="form-row">@foreach(['username'=>'Usuario','email'=>'Correo','password'=>'Contraseña'] as $field=>$label)<div class="form-group col-md-4"><label>{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ $field !== 'password' ? ($editUser->$field ?? '') : '' }}" type="{{ $field === 'password' ? 'password' : 'text' }}" {{ $field !== 'password' ? 'required' : '' }}></div>@endforeach
                <div class="form-group col-md-2"><label>Estado</label><select class="form-control" name="status"><option value="10">Activo</option><option value="9">Inactivo</option></select></div>
                <div class="form-group col-md-2"><label>Rol principal</label><select class="form-control" name="role"><option>admin</option><option>psicologo</option><option>tutor</option><option>alumno</option></select></div>
            </div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> {{ $editUser ? 'Guardar cambios' : 'Crear cuenta' }}</button>
        </div>
    </form>
    <div class="card ui-table-card"><div class="table-responsive"><table class="table ui-table mb-0"><thead><tr><th>Cuenta</th><th>Correo</th><th>Estado</th><th>Roles</th><th class="text-right">Acciones</th></tr></thead><tbody>
    @forelse($users as $user)<tr><td><strong>{{ $user->username }}</strong><small class="d-block text-muted">ID {{ $user->id }}</small></td><td>{{ $user->email }}</td><td><x-ui.status-badge :status="$user->isActive() ? 'active' : 'inactive'" :label="$user->isActive() ? 'Activo' : 'Inactivo'" /></td><td>{{ $roles[(string) $user->id] ?? 'Sin rol' }}</td><td class="text-right"><x-ui.action-menu><a class="dropdown-item" href="{{ url('/admin/users?edit='.$user->id) }}"><i class="fas fa-edit"></i> Editar</a><form method="POST" action="{{ url('/admin/users/'.$user->id.'/delete') }}" onsubmit="return confirm('¿Desactivar esta cuenta?');">@csrf<button class="dropdown-item text-danger"><i class="fas fa-user-slash"></i> Desactivar</button></form></x-ui.action-menu></td></tr>@empty
        <tr><td colspan="5" class="ui-empty-state">No hay cuentas registradas.</td></tr>
    @endforelse
    </tbody></table></div><div class="card-body">{{ $users->links() }}</div></div>
</div>
@endsection
