@extends('layouts.app')
@section('title', 'Estados, municipios y listas')
@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Opciones del expediente</li>
@endsection
@section('content')
<div class="ui-resource-page">
    <x-ui.page-header eyebrow="Catálogos del expediente" title="Estados, municipios y listas" description="Agrega opciones y edita los nombres que ven los alumnos en su expediente.">
        <a class="btn btn-outline-primary" href="{{ url('/expediente-pregunta/index') }}"><i class="fas fa-list-alt" aria-hidden="true"></i> Diseñar expediente</a>
    </x-ui.page-header>
    @if($errors->any())
        <div class="alert alert-danger" role="alert"><strong>No se guardaron los cambios.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="get" class="card ui-form-card mb-4" action="{{ route('admin.expediente-catalogos') }}">
        <div class="card-body">
            <div class="form-row align-items-end">
                <div class="form-group col-md-{{ $esMunicipio ? '6' : '9' }} mb-md-0">
                    <label for="catalogo-lista">Lista a editar</label>
                    <select class="form-control" id="catalogo-lista" name="lista">
                        @foreach($listas->groupBy('seccion') as $seccion => $preguntas)
                            <optgroup label="{{ $seccion }}">@foreach($preguntas as $lista)<option value="{{ $lista->clave }}" @selected($lista->id === $pregunta->id)>{{ $lista->titulo }}{{ !$lista->activo ? ' (oculta)' : '' }}</option>@endforeach</optgroup>
                        @endforeach
                    </select>
                </div>
                @if($esMunicipio)
                    <div class="form-group col-md-3 mb-md-0"><label for="catalogo-estado">Estado</label><select class="form-control" id="catalogo-estado" name="estado">@foreach($estados->opciones as $valor)<option value="{{ $valor }}" @selected($estado === $valor)>{{ $estados->configuracion['etiquetas'][$valor] ?? $valor }}</option>@endforeach</select></div>
                @endif
                <div class="col-md-3"><button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-hidden="true"></i> Consultar lista</button></div>
            </div>
        </div>
    </form>
    <form method="post" action="{{ route('admin.expediente-catalogos.save', $pregunta->id) }}" class="ui-catalog-editor">
        @csrf
        <input type="hidden" name="revision" value="{{ $revision }}">
        @if($esMunicipio)<input type="hidden" name="estado" value="{{ $estado }}">@endif
        <div class="card ui-form-card">
            <div class="card-body">
                <h2 class="ui-form-card__title"><i class="fas fa-plus-circle" aria-hidden="true"></i> Agregar opciones</h2>
                <p class="ui-form-intro">{{ $esMunicipio ? 'Agrega municipios o localidades al estado seleccionado.' : 'Escribe las opciones que deseas añadir a esta lista.' }}</p>
                <label for="nuevas-opciones">Una opción por línea</label>
                <textarea class="form-control" id="nuevas-opciones" name="nuevas" rows="6" placeholder="Escribe aquí las nuevas opciones…">{{ old('nuevas') }}</textarea>
                <p class="form-text mt-3 mb-0">Los cambios se aplican al pulsar Guardar cambios.</p>
            </div>
        </div>
        <section class="card ui-form-card ui-catalog-options">
            <div class="ui-panel-heading"><div><h2>{{ $pregunta->titulo }}</h2><p>{{ $esMunicipio ? ($estados->configuracion['etiquetas'][$estado] ?? $estado) : $pregunta->seccion }}</p></div><span class="ui-count">{{ count($opciones) }} opciones</span></div>
            <div class="card-body py-3"><label class="sr-only" for="buscar-opciones">Buscar en esta lista</label><input id="buscar-opciones" type="search" class="form-control" placeholder="Buscar una opción en esta lista…"></div>
            <div class="table-responsive ui-options-scroll">
                <table class="table ui-table mb-0">
                    <thead><tr><th scope="col">Nombre visible para el alumno</th><th scope="col" class="text-center">Quitar</th></tr></thead>
                    <tbody>
                        @forelse($opciones as $index => $valor)
                            <tr data-option-row>
                                <td><label class="sr-only" for="opcion-{{ $index }}">Nombre de {{ $pregunta->configuracion['etiquetas'][$valor] ?? $valor }}</label><input class="form-control" id="opcion-{{ $index }}" name="etiquetas[{{ $index }}]" value="{{ old('etiquetas.'.$index, $pregunta->configuracion['etiquetas'][$valor] ?? $valor) }}" maxlength="255" required></td>
                                <td class="text-center"><input type="checkbox" name="quitar[]" value="{{ $index }}" aria-label="Quitar {{ $pregunta->configuracion['etiquetas'][$valor] ?? $valor }}" @checked(in_array((string) $index, array_map('strval', old('quitar', [])), true))></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="ui-empty-state"><strong>No hay opciones en esta lista</strong>Agrega las primeras opciones con el formulario.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="ui-options-no-results" id="opciones-sin-resultados" hidden>No hay opciones que coincidan con tu búsqueda.</p>
            <div class="ui-panel-footer"><p>Editar los nombres conserva las respuestas anteriores. Las opciones relacionadas con otras preguntas no se pueden quitar.</p></div>
        </section>
        <div class="ui-catalog-save"><button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Guardar cambios</button><a class="btn btn-outline-secondary" href="{{ route('admin.expediente-catalogos', ['lista' => $pregunta->clave, 'estado' => $estado]) }}">Cancelar</a></div>
    </form>
    @if($esMunicipio)<p class="text-muted small mt-3">Municipios iniciales: <a href="https://www.inegi.org.mx/servicios/catalogounico.html" target="_blank" rel="noopener">Catálogo Único del INEGI</a>. Puedes agregar localidades adicionales.</p>@endif
</div>
@endsection
@push('scripts')
<script>
document.getElementById('catalogo-lista').addEventListener('change', function () {
    const state = this.form.querySelector('[name="estado"]');
    if (state) state.disabled = true;
    this.form.requestSubmit();
});
document.getElementById('buscar-opciones').addEventListener('input', function () {
    const term = this.value.trim().toLocaleLowerCase('es');
    const rows = [...document.querySelectorAll('[data-option-row]')];
    rows.forEach(row => { row.hidden = !row.querySelector('input[type="text"], input:not([type])').value.toLocaleLowerCase('es').includes(term); });
    document.getElementById('opciones-sin-resultados').hidden = !rows.length || rows.some(row => !row.hidden);
});
</script>
@endpush
