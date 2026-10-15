CONTROL DE TUTORÍAS ITSVA

NO HAS HECHO TU ACTUALIZACIÓN DE EXPEDIENTE

Tu expediente tutorial está pendiente de completar o actualizar.
Ingresa al sistema para revisar y enviar tu información:
{!! $formularioUrl !!}
@if (!empty($modoNotificacionPrueba))
SIMULACIÓN: este mensaje se envió únicamente a {!! $testEmail !!}. Ningún alumno recibió este correo.
@endif
Grupo: {!! $grupo->carrera->nombre . ' · ' . $grupo->semestre->nombre . ' · ' . $grupo->grupoLetra->letra_key !!}
Alumnos que recibirían este aviso: {!! (int) $total !!}
@foreach ($pendientes as $item)
- {!! $item['alumno']->nombre_completo !!} ({!! $item['alumno']->matricula !!}): {!! $item['motivo'] !!}
@endforeach

Si ya actualizaste tu expediente, ignora este mensaje o comunícate con tu tutor.
