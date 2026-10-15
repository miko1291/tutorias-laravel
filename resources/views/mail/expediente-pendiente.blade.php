<div style="font-family:Arial,sans-serif;color:#263d53;max-width:620px;margin:auto;border:1px solid #dce4eb">
    <div style="background:#00325f;color:#fff;padding:24px 28px">
        <div style="font-size:11px;letter-spacing:1.4px">ITSVA · PROGRAMA INSTITUCIONAL DE TUTORÍAS</div>
        <h1 style="font-size:23px;margin:12px 0 0">NO HAS HECHO TU ACTUALIZACIÓN DE EXPEDIENTE</h1>
    </div>
    <div style="padding:25px 28px">
        <p>Hola. Tu expediente tutorial está pendiente de completar o actualizar.</p>
        <p>Ingresa al sistema para revisar y enviar tu información.</p>
        <p style="margin:22px 0">
            <a href="{{ $formularioUrl }}" style="display:inline-block;background:#00325f;color:#fff;text-decoration:none;padding:11px 18px;border-radius:4px">Actualizar expediente</a>
        </p>
        @if (!empty($modoNotificacionPrueba))
            <div style="padding:12px 15px;background:#f6f0df;border-left:4px solid #b28a31;color:#5d4817;font-size:13px">
                Simulación enviada únicamente a {{ $testEmail }}. Ningún alumno recibió este correo.
            </div>
        @endif
        <p>Grupo: <strong>{{ $grupo->carrera->nombre . ' · ' . $grupo->semestre->nombre . ' · ' . $grupo->grupoLetra->letra_key }}</strong></p>
        <p>Alumnos que recibirían este aviso: <strong>{{ (int) $total }}</strong></p>
        <table style="width:100%;border-collapse:collapse;margin-top:18px"><tr style="background:#f1f4f7"><th style="padding:8px;text-align:left">Alumno</th><th style="padding:8px;text-align:left">Matrícula</th><th style="padding:8px;text-align:left">Situación</th></tr>
        @foreach ($pendientes as $item)<tr><td style="border-top:1px solid #e2e8ed;padding:8px">{{ $item['alumno']->nombre_completo }}</td><td style="border-top:1px solid #e2e8ed;padding:8px">{{ $item['alumno']->matricula }}</td><td style="border-top:1px solid #e2e8ed;padding:8px">{{ $item['motivo'] }}</td></tr>@endforeach</table>
        <p style="color:#718092;font-size:12px;margin-top:24px">Si ya actualizaste tu expediente, ignora este mensaje o comunícate con tu tutor.</p>
    </div>
</div>
