@extends('layouts.app')
@section('title', $title)
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Catálogos" :title="$title" description="Consulta y agrega registros al catálogo." />
    <div class="{{ count($fields) <= 2 ? 'ui-catalog-layout' : '' }}">
    <form method="POST" action="{{ url('/admin/catalogos/'.$catalog.'/save') }}" class="card ui-form-card mb-4">
        @csrf
        <div class="card-body">
            <h2 class="ui-form-card__title"><i class="fas fa-plus-circle" aria-hidden="true"></i> Nuevo registro</h2>
            <p class="ui-form-intro">Completa los datos para agregar un registro al catálogo.</p>
            <div class="form-row">
                @foreach($fields as $field => $rules)
                    @php
                        $label = ['letra_key' => 'Letra del grupo', 'num_semestre' => 'Número de semestre', 'id_estatus' => 'ID del estatus', 'id_ciclo' => 'ID del ciclo escolar', 'date_start' => 'Fecha de inicio', 'date_end' => 'Fecha de fin', 'fecha_inicial' => 'Fecha de inicio', 'fecha_final' => 'Fecha de fin'][$field] ?? ucfirst(str_replace('_', ' ', $field));
                        $type = str_contains($field, 'fecha') || str_contains($field, 'date_') ? 'date' : (in_array('integer', $rules, true) ? 'number' : 'text');
                    @endphp
                    <div class="form-group col-md-4">
                        <label for="catalog-{{ $field }}">{{ $label }}</label>
                        <input class="form-control @error($field) is-invalid @enderror" id="catalog-{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" required>
                        @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>
            <div class="ui-form-actions"><button class="btn btn-primary" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> Crear registro</button></div>
        </div>
    </form>
    <x-ui.list-panel title="Registros del catálogo" :paginator="$items">
        <table class="table ui-table">
            <thead><tr>
                @foreach(array_keys($fields) as $field)
                    <th scope="col">{{ ['letra_key' => 'Letra del grupo', 'num_semestre' => 'Semestre', 'id_estatus' => 'ID del estatus', 'id_ciclo' => 'ID del ciclo', 'date_start' => 'Inicio', 'date_end' => 'Fin'][$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</th>
                @endforeach
                <th scope="col" class="text-right">Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        @foreach(array_keys($fields) as $field)<td>{{ $item->$field }}</td>@endforeach
                        <td><div class="ui-inline-actions"><form method="POST" action="{{ url('/admin/catalogos/'.$catalog.'/'.$item->id.'/delete') }}" onsubmit="return confirm('¿Eliminar este registro del catálogo?')">@csrf<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($fields) + 1 }}" class="ui-empty-state"><strong>Aún no hay registros</strong>Agrega el primer registro con el formulario de arriba.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.list-panel>
    </div>
</div>
@endsection
