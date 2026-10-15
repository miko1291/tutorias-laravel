@php
    $settings = $pregunta->configuracion ?? [];
    $value = old('respuestas.'.$pregunta->clave, $respuestas[$pregunta->clave] ?? '');
    $scalar = is_scalar($value) ? (string) $value : '';
    $selected = is_array($value) ? array_map('strval', $value) : [$scalar];
    $fieldId = 'answer-'.$pregunta->clave;
@endphp
<div class="ef-question" data-key="{{ $pregunta->clave }}" data-required="{{ $pregunta->requerido ? '1' : '0' }}" data-condition="{{ json_encode($settings['condicion'] ?? null) }}">
    <label class="ef-question-title" for="{{ $fieldId }}">{{ $pregunta->titulo }} @if($pregunta->requerido)<span class="ef-required" aria-label="obligatoria">*</span>@endif</label>
    @if($pregunta->descripcion)<p class="ef-description">{{ $pregunta->descripcion }}</p>@endif
    @if(in_array($pregunta->tipo, ['radio', 'checkbox'], true))
        <div class="ef-options" role="group" aria-label="{{ $pregunta->titulo }}">
            @foreach($pregunta->opciones as $option)
                <label class="ef-option" for="{{ $fieldId }}-{{ $loop->index }}">
                    <input id="{{ $fieldId }}-{{ $loop->index }}" type="{{ $pregunta->tipo }}" name="respuestas[{{ $pregunta->clave }}]{{ $pregunta->tipo === 'checkbox' ? '[]' : '' }}" value="{{ $option }}" @checked(in_array((string) $option, $selected, true)) @required($pregunta->tipo === 'radio' && $pregunta->requerido && $loop->first)>
                    <span>{{ $settings['etiquetas'][$option] ?? $option }}</span>
                </label>
            @endforeach
        </div>
    @elseif($pregunta->tipo === 'select')
        <select class="ef-control" id="{{ $fieldId }}" name="respuestas[{{ $pregunta->clave }}]" @required($pregunta->requerido)>
            <option value="">Selecciona una opción</option>
            @foreach($pregunta->opciones as $option)<option value="{{ $option }}" @selected((string) $option === $scalar)>{{ $settings['etiquetas'][$option] ?? $option }}</option>@endforeach
        </select>
    @elseif($pregunta->tipo === 'textarea')
        <textarea class="ef-control" id="{{ $fieldId }}" name="respuestas[{{ $pregunta->clave }}]" rows="3" maxlength="{{ $settings['maxlength'] ?? 10000 }}" placeholder="{{ $settings['placeholder'] ?? 'Escribe tu respuesta' }}" @required($pregunta->requerido)>{{ $scalar }}</textarea>
    @else
        <input class="ef-control" id="{{ $fieldId }}" type="{{ $pregunta->tipo }}" name="respuestas[{{ $pregunta->clave }}]" value="{{ $scalar }}" placeholder="{{ $settings['placeholder'] ?? 'Escribe tu respuesta' }}" @required($pregunta->requerido)
            @if(isset($settings['min'])) min="{{ $settings['min'] === 'today' ? now()->toDateString() : $settings['min'] }}" @endif
            @if(isset($settings['max'])) max="{{ $settings['max'] === 'today' ? now()->toDateString() : $settings['max'] }}" @endif
            @if(in_array($pregunta->tipo, ['text', 'email', 'tel'], true)) maxlength="{{ $settings['maxlength'] ?? 255 }}" @endif
            @if(!empty($settings['pattern'])) pattern="{{ $settings['pattern'] }}" @endif>
    @endif
    @error('respuestas.'.$pregunta->clave)<p class="text-danger mt-2" role="alert">{{ $message }}</p>@enderror
</div>
