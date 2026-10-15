<div style="font-family:Arial,sans-serif;color:#263d53;max-width:600px;margin:auto;border:1px solid #dce4eb">
    <div style="background:#00325f;color:#fff;padding:22px 26px">
        <div style="font-size:11px;letter-spacing:1.3px">ITSVA · CONTROL DE TUTORÍAS</div>
        <h1 style="font-size:22px;margin:11px 0 0">{{ $title }}</h1>
    </div>
    <div style="padding:25px 26px;line-height:1.6">
        <p>{!! nl2br(e($contenido)) !!}</p>
        @if ($url)
            <p><a href="{{ $url }}" style="display:inline-block;background:#00325f;color:#fff;text-decoration:none;padding:10px 17px;border-radius:4px">Abrir notificación</a></p>
        @endif
    </div>
    <div style="padding:14px 26px;background:#f8fafc;border-top:1px solid #e8eef3;color:#8795a3;font-size:12px">Mensaje automático. No respondas a este correo.</div>
</div>
