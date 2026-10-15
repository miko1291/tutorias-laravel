@extends('layouts.app')
@section('title', $title)
@section('content')
<h1>{{ $title }}</h1>
<form method="POST" action="{{ url('/admin/catalogos/'.$catalog.'/save') }}" class="card card-body mb-4">@csrf
<div class="form-row">@foreach($fields as $field=>$rules)<div class="form-group col-md-4"><label>{{ ucfirst(str_replace('_',' ',$field)) }}</label><input class="form-control" name="{{ $field }}" type="{{ str_contains($field, 'fecha') || str_contains($field, 'date_') ? 'date' : 'text' }}" required></div>@endforeach</div><button class="btn btn-primary">Crear</button></form>
<div class="card"><table class="table mb-0"><thead><tr>@foreach(array_keys($fields) as $field)<th>{{ ucfirst(str_replace('_',' ',$field)) }}</th>@endforeach<th></th></tr></thead><tbody>@foreach($items as $item)<tr>@foreach(array_keys($fields) as $field)<td>{{ $item->$field }}</td>@endforeach<td><form method="POST" action="{{ url('/admin/catalogos/'.$catalog.'/'.$item->id.'/delete') }}">@csrf<button class="btn btn-link text-danger">Eliminar</button></form></td></tr>@endforeach</tbody></table></div>{{ $items->links() }}
@endsection
