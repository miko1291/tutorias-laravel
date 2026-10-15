@php
    /** @var \App\Models\Alumno $model */
    /** @var string $accion */
@endphp

<div class="alumno-form ui-form-card card">
    @if ($errors->any())
        <div class="alert ui-alert alert-danger m-3" role="alert">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i><ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ $accion }}" class="card-body">
        @csrf
        <div class="ui-form-card__title"><i class="fas fa-id-card"></i> Información del estudiante</div>
        <p class="text-muted small">Completa los campos obligatorios para mantener la ficha académica actualizada.</p>
        <div class="form-row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-matricula">Matrícula</label>
                    <input type="text" id="alumno-matricula" class="form-control @error('matricula') is-invalid @enderror" name="matricula" value="{{ old('matricula', $model->matricula) }}" placeholder="Ej. 20240001" required>
                    @error('matricula')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-nombres">Nombres</label>
                    <input type="text" id="alumno-nombres" class="form-control @error('nombres') is-invalid @enderror" name="nombres" value="{{ old('nombres', $model->nombres) }}" placeholder="Nombre(s)" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-apellidop">Apellido paterno</label>
                    <input type="text" id="alumno-apellidop" class="form-control" name="apellidop" value="{{ old('apellidop', $model->apellidop) }}" placeholder="Apellido paterno" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-apellidom">Apellido materno</label>
                    <input type="text" id="alumno-apellidom" class="form-control" name="apellidom" value="{{ old('apellidom', $model->apellidom) }}" placeholder="Apellido materno" required>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-correo">Correo</label>
                    <input type="email" id="alumno-correo" class="form-control" name="correo" value="{{ old('correo', $model->correo) }}" placeholder="Correo electrónico">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-telefono">Teléfono</label>
                    <input type="tel" id="alumno-telefono" class="form-control" name="telefono" value="{{ old('telefono', $model->telefono) }}" placeholder="Número de teléfono" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-fecha_nac">Fecha de nacimiento</label>
                    <input type="date" id="alumno-fecha_nac" class="form-control form-control-md" name="fecha_nac" value="{{ old('fecha_nac', $model->fecha_nac) }}" placeholder="Fecha de nacimiento...">
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="col-md-8">
                <div class="form-group">
                    <label class="control-label" for="alumno-ciudad">Ciudad o localidad</label>
                    <input type="text" id="alumno-ciudad" class="form-control" name="ciudad" value="{{ old('ciudad', $model->ciudad) }}" placeholder="Ciudad">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label" for="alumno-genero">Género</label>
                    <select id="alumno-genero" class="form-control form-control-md" name="genero">
                        <option value="">Selecciona una opción</option>
                        <option value="0" @selected(old('genero', (string) $model->genero) === '0')>MASCULINO</option>
                        <option value="1" @selected(old('genero', (string) $model->genero) === '1')>FEMENINO</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar información</button>
        </div>
    </form>
</div>
