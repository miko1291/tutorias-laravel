@php
    /** @var \App\Models\Alumno $alumno */
    /** @var \App\Models\AlumnoExpediente|null $expediente */
    /** @var array $respuestas */
    /** @var bool $allowEdit */
    // Estas claves identifican respuestas guardadas; no indican la posición de la pregunta.
    $fieldLabels = [
        'fecha_nacimiento' => 'Fecha de nacimiento',
        'campo_002' => 'Fecha de actualización',
        'campo_004' => 'Correo institucional',
        'campo_006' => 'Sexo',
        'campo_007' => 'Edad',
        'campo_008' => 'Edad',
        'campo_009' => 'Teléfono personal',
        'campo_010' => 'Estado de procedencia',
        'campo_011' => 'Estado especificado',
        'campo_012' => 'Municipio o localidad',
        'campo_013' => 'Municipio especificado',
        'campo_014' => 'Porcentaje aproximado de lengua maya',
        'campo_015' => 'Carrera',
        'campo_016' => 'Semestre',
        'campo_017' => 'Grupo',
        'campo_018' => 'Asignaturas en repetición o curso especial',
        'campo_019' => 'Asignaturas para reforzamiento',
        'campo_020' => 'Nivel de inglés',
        'campo_021' => 'Otra barrera académica',
        'campo_022' => 'Grupo sanguíneo',
        'campo_023' => 'Discapacidad especificada',
        'campo_024' => 'Enfermedad o alergia especificada',
        'campo_025' => 'Parentesco del contacto de emergencia',
        'campo_026' => 'Nombre del contacto de emergencia',
        'campo_027' => 'Teléfono del contacto de emergencia',
        'campo_028' => 'Otra discapacidad física',
        'campo_029' => 'Otra discapacidad sensorial',
        'campo_030' => 'Otra discapacidad intelectual',
        'campo_031' => 'Otra discapacidad psíquica',
        'campo_032' => 'Otra discapacidad visceral',
        'etnia' => '¿Pertenece a alguna etnia Maya?', 'hablante' => '¿Es maya hablante?',
        'papa_estado' => 'Situación del padre', 'papa_estudios' => 'Escolaridad del padre',
        'papa_trabajo' => 'Lugar de trabajo del padre', 'mama_estado' => 'Situación de la madre',
        'mama_estudios' => 'Escolaridad de la madre', 'mama_trabajo' => 'Lugar de trabajo de la madre',
        'req_reforzamiento' => 'Requiere reforzamiento académico', 'sol_reforzamiento' => 'Solicita reforzamiento',
        'idiomas' => 'Idiomas que habla', 'cambridge' => 'Certificación Cambridge',
        'tipo_sangre' => 'Grupo sanguíneo', 'tipo_sangre_otro' => 'Otro grupo sanguíneo',
        'enfermedad' => 'Enfermedad actual', 'enfermedades' => 'Enfermedades relevantes',
        'infecciosas' => 'Enfermedades infecciosas', 'alergia_medicamentos' => 'Alergias a medicamentos',
        'cirugias' => 'Cirugías', 'seguro' => 'Servicio médico', 'contacto_accidente' => 'Contacto en caso de accidente',
        'discapacidad' => 'Discapacidad', 'barreras' => 'Barreras para el aprendizaje',
        'disc_fisica' => 'Discapacidad física', 'disc_fisica_tipo' => 'Tipo de discapacidad física',
        'disc_sensorial' => 'Discapacidad sensorial', 'disc_sensorial_tipo' => 'Tipo de discapacidad sensorial',
        'disc_intelectual' => 'Discapacidad intelectual', 'disc_intelectual_tipo' => 'Tipo de discapacidad intelectual',
        'disc_psiquica' => 'Discapacidad psíquica', 'disc_psiquica_tipo' => 'Tipo de discapacidad psíquica',
        'disc_visceral' => 'Discapacidad visceral', 'disc_visceral_tipo' => 'Tipo de discapacidad visceral',
        'disc_multiple' => 'Discapacidad múltiple', 'disc_no_contemplada' => 'Otra discapacidad',
        'vivienda' => 'Tipo de vivienda', 'ingreso_familiar' => 'Ingreso familiar',
        'gastos' => 'Gastos familiares', 'automoviles' => 'Automóviles en la familia',
        'telefono_padres' => 'Teléfono de padres o tutores', 'trabaja' => 'Trabaja actualmente',
        'turno_trabajo' => 'Turno de trabajo', 'empresa' => 'Empresa o lugar de trabajo',
        'habitos_estudio' => 'Hábitos de estudio',
    ];
    $questionDefinitions = \App\Models\ExpedientePregunta::withTrashed()->get()->keyBy('clave');
    $fieldLabels = array_replace($fieldLabels, $questionDefinitions->pluck('titulo', 'clave')->all());
    foreach ($respuestas as $key => &$answer) {
        $optionLabels = $questionDefinitions->get($key)?->configuracion['etiquetas'] ?? [];
        if (is_array($answer)) {
            $answer = array_map(static fn ($value) => $optionLabels[$value] ?? $value, $answer);
        } elseif (is_scalar($answer)) {
            $answer = $optionLabels[$answer] ?? $answer;
        }
    }
    unset($answer);
    $fechaNacimiento = (string) ($respuestas['fecha_nacimiento'] ?? $alumno->fecha_nac ?? '');
    if ($fechaNacimiento !== '') {
        $respuestas['fecha_nacimiento'] = $fechaNacimiento;
        if ($fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $fechaNacimiento)) {
            $respuestas['campo_007'] = $fecha->diff(new \DateTimeImmutable('today'))->y;
        }
    }
    $likertLabels = [
        'likert_1' => 'Me concentro bien en lo que hago',
        'likert_2' => 'Mis preocupaciones me han hecho perder mucho sueño',
        'likert_3' => 'Me siento útil en la vida',
        'likert_4' => 'Me siento capaz de tomar decisiones',
        'likert_5' => 'Me siento constantemente agobiado y en tensión',
        'likert_6' => 'Me siento capaz de superar mis dificultades',
        'likert_7' => 'Puedo disfrutar mis actividades normales de cada día',
        'likert_8' => 'He sido capaz de hacer frente a mis problemas',
        'likert_9' => 'Me he sentido poco feliz y deprimido',
        'likert_10' => 'Siento confianza en mí mismo',
    ];

    $sections = [
        'Datos personales' => [], 'Procedencia y contexto' => [], 'Datos culturales' => [], 'Datos familiares' => [],
        'Datos académicos' => [], 'Situación académica y laboral' => [],
        'Reforzamiento e idiomas' => [], 'Salud y contacto de emergencia' => [],
        'Bienestar y discapacidades' => [], 'Situación socioeconómica' => [],
        'Hábitos de estudio' => [], 'Valoración de riesgo académico' => [],
    ];
    $sectionFor = static function ($key) {
        if (strpos($key, 'likert_') === 0) return 'Valoración de riesgo académico';
        if (in_array($key, ['idiomas', 'cambridge', 'req_reforzamiento', 'sol_reforzamiento'], true)) return 'Reforzamiento e idiomas';
        if (in_array($key, ['etnia', 'hablante', 'campo_014'], true)) return 'Datos culturales';
        if (in_array($key, ['papa_estado', 'papa_estudios', 'papa_trabajo', 'mama_estado', 'mama_estudios', 'mama_trabajo', 'telefono_padres'], true)) return 'Datos familiares';
        if (in_array($key, ['tipo_sangre', 'tipo_sangre_otro', 'enfermedad', 'enfermedades', 'infecciosas', 'alergia_medicamentos', 'cirugias', 'seguro', 'contacto_accidente'], true)) return 'Salud y contacto de emergencia';
        if (strpos($key, 'disc_') === 0 || in_array($key, ['discapacidad', 'barreras'], true)) return 'Bienestar y discapacidades';
        if (in_array($key, ['vivienda', 'ingreso_familiar', 'gastos', 'automoviles'], true)) return 'Situación socioeconómica';
        if ($key === 'habitos_estudio') return 'Hábitos de estudio';
        if (in_array($key, ['fecha_nacimiento', 'campo_002', 'campo_004', 'campo_006', 'campo_007', 'campo_008', 'campo_009'], true)) return 'Datos personales';
        if (in_array($key, ['campo_010', 'campo_011', 'campo_012', 'campo_013'], true)) return 'Procedencia y contexto';
        if ($key === 'campo_014') return 'Datos culturales';
        if (in_array($key, ['campo_015', 'campo_016', 'campo_017'], true)) return 'Datos académicos';
        if (in_array($key, ['campo_018', 'campo_019', 'campo_020', 'campo_021'], true)) return 'Situación académica y laboral';
        if ($key === 'campo_022' || in_array($key, ['campo_023', 'campo_024', 'campo_025', 'campo_026', 'campo_027'], true)) return 'Salud y contacto de emergencia';
        if (in_array($key, ['campo_028', 'campo_029', 'campo_030', 'campo_031', 'campo_032'], true)) return 'Bienestar y discapacidades';
        return 'Situación académica y laboral';
    };
    foreach ($respuestas as $key => $value) {
        if ($value === '' || $value === null || $value === []) continue;
        if ($key === 'tipo_sangre' && array_key_exists('campo_022', $respuestas)) continue;
        if ($key === 'campo_008' && array_key_exists('campo_007', $respuestas)) continue;
        if ($key === 'discapacidad' && collect(array_keys($respuestas))->contains(
            static fn ($responseKey) => strpos((string) $responseKey, 'disc_') === 0
        )) continue;
        $section = $sectionFor((string) $key);
        if (!isset($sections[$section])) $sections[$section] = [];
        $sections[$section][$key] = $value;
    }

    $fieldOrder = [
        'Salud y contacto de emergencia' => [
            'campo_025' => 10, 'campo_026' => 20, 'campo_027' => 30,
            'campo_022' => 40, 'enfermedad' => 50, 'campo_024' => 60,
            'alergia_medicamentos' => 70, 'enfermedades' => 80,
            'infecciosas' => 90, 'cirugias' => 100, 'seguro' => 110,
            'contacto_accidente' => 120,
        ],
        'Datos personales' => ['fecha_nacimiento' => 10, 'campo_004' => 20, 'campo_009' => 30, 'campo_006' => 40, 'campo_007' => 50, 'campo_008' => 60, 'campo_002' => 70],
        'Datos académicos' => ['campo_015' => 10, 'campo_016' => 20, 'campo_017' => 30],
    ];
    foreach ($sections as $section => &$fields) {
        if (isset($fieldOrder[$section])) {
            uksort($fields, static function ($a, $b) use ($fieldOrder, $section) {
                return ($fieldOrder[$section][$a] ?? 999) <=> ($fieldOrder[$section][$b] ?? 999);
            });
        }
    }
    unset($fields);
@endphp
<div class="expediente-ficha">
    <div class="ficha-header">
        <div><div class="ficha-kicker">ITSVA · Expediente estudiantil</div><h1>{{ $alumno->nombre_completo }}</h1><p>Consulta de información registrada</p></div>
        <div class="ficha-meta"><span>Matrícula</span><strong>{{ $alumno->matricula }}</strong><span>Estado</span><strong>{{ $expediente ? $expediente->estatus : 'Sin captura' }}</strong></div>
    </div>
    @if (!$expediente)
        <div class="alert alert-info mt-3">El alumno todavía no ha enviado su expediente.</div>
    @else
        <div class="ficha-updated">Versión {{ (int) $expediente->version }} · Última actualización: {{ $expediente->updated_at }}</div>
        <div class="ficha-body">
            <nav class="ficha-nav" aria-label="Secciones del expediente">
                <p class="ficha-nav__title">Secciones</p>
                <ol>
                    @foreach ($sections as $section => $fields)
                        @if (!$fields) @continue @endif
                        <li><a href="#ficha-{{ \Illuminate\Support\Str::slug($section) }}">{{ $section }}</a></li>
                    @endforeach
                </ol>
            </nav>
            <div class="ficha-content">
                @foreach ($sections as $section => $fields)
                    @if (!$fields) @continue @endif
                    <section id="ficha-{{ \Illuminate\Support\Str::slug($section) }}" class="ficha-section {{ $section === 'Salud y contacto de emergencia' ? 'ficha-section--health' : '' }}"><h2>{{ $section }}</h2><div class="ficha-grid">
                        @foreach ($fields as $key => $value)
                            @php
                                $values = is_array($value) ? array_filter($value, static fn($v) => $v !== '') : [(string) $value];
                                if (!$values) $values = ['Ninguna'];
                            @endphp
                            <article class="ficha-field"><div class="ficha-label">{{ $likertLabels[$key] ?? $fieldLabels[$key] ?? preg_replace('/^campo_/', 'Información registrada ', ucwords(str_replace('_', ' ', $key))) }}</div><div class="ficha-value">@foreach ($values as $item)<span class="ficha-badge">{!! nl2br(e((string) $item)) !!}</span>@endforeach</div></article>
                        @endforeach
                    </div></section>
                @endforeach
            </div>
        </div>
    @endif
    @if ($allowEdit)<div class="ficha-actions"><a class="btn btn-primary" href="{{ url('/expediente/index') }}"><i class="fas fa-edit"></i> Actualizar expediente</a></div>@endif
</div>
@push('styles')
<style>
.expediente-ficha{max-width:1080px;margin:20px auto 50px;color:#27364a}.ficha-header{display:flex;justify-content:space-between;gap:25px;align-items:flex-start;padding:32px 38px;border-radius:16px;background:linear-gradient(120deg,#173b63,#2f6fb0);color:#fff;box-shadow:0 12px 28px rgba(28,58,94,.16)}.ficha-kicker{text-transform:uppercase;letter-spacing:.12em;font-size:.72rem;opacity:.82}.ficha-header h1{margin:7px 0;font-size:2rem;color:#fff!important}.ficha-header p{margin:0;color:rgba(255,255,255,.9)!important;opacity:1}.ficha-meta{display:grid;grid-template-columns:auto auto;gap:4px 18px;padding:12px 16px;border:1px solid rgba(255,255,255,.3);border-radius:8px;min-width:190px;font-size:.85rem}.ficha-meta span{opacity:.75}.ficha-meta strong{text-align:right}.ficha-updated{margin:16px 0 5px;color:#68788d;font-size:.85rem}.ficha-section{margin-top:18px;padding:24px;background:#fff;border:1px solid #dfe6ed;border-radius:12px;box-shadow:0 5px 18px rgba(28,58,94,.05)}.ficha-section h2{margin:0 0 18px;padding-bottom:11px;border-bottom:2px solid #e3e9ef;color:var(--tecm-blue);font-size:1.15rem}.ficha-section--health{border-top:4px solid #b28a31}.ficha-section--health h2{color:#6f551b}.ficha-section--health .ficha-field:nth-child(-n+3){background:#fffaf0;border-color:#eadfbd}.ficha-section:last-of-type h2:after{content:'  ·  1 = totalmente en desacuerdo  |  3 = neutral  |  5 = totalmente de acuerdo';display:block;margin-top:7px;color:#7a8b9b;font-size:.72rem;font-weight:400}.ficha-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.ficha-field{padding:13px 15px;border:1px solid #edf1f5;border-radius:8px;background:#fbfcfe}.ficha-label{font-size:.74rem;text-transform:uppercase;letter-spacing:.05em;color:#61758a;margin-bottom:7px}.ficha-value{color:#27364a;line-height:1.5}.ficha-badge{display:inline-block;margin:0 5px 4px 0;padding:4px 9px;border-radius:6px;background:#f0f2f4;color:#263d53;font-size:.9rem;border:1px solid #d9e0e6}.ficha-section--health .ficha-badge{background:#f6efd9;border-color:#e4d5a8;color:#5d4817;font-weight:600}.ficha-actions{margin-top:20px;display:flex;justify-content:flex-end}@media(max-width:700px){.ficha-header{display:block;padding:25px}.ficha-meta{margin-top:18px}.ficha-grid{grid-template-columns:1fr}}
</style>
<style>
.expediente-ficha{max-width:1160px;margin:8px auto 45px}.ficha-header{border-radius:6px;background:var(--tecm-blue);box-shadow:0 2px 8px rgba(28,58,94,.12);border-left:5px solid var(--tecm-blue);padding:28px 32px}.ficha-header h1{font-size:1.8rem}.ficha-meta{border-radius:4px}.ficha-updated{padding:10px 12px;background:#f4f8fc;border-left:3px solid var(--tecm-blue);color:#52708c}.ficha-section{border-radius:6px;box-shadow:0 2px 8px rgba(28,58,94,.05);padding:21px}.ficha-section h2{color:var(--tecm-blue);border-bottom-color:#cbdceb}.ficha-section--health{border-top-color:var(--tecm-blue)}.ficha-section--health h2{color:var(--tecm-blue)}.ficha-section--health .ficha-field:nth-child(-n+3){background:#f4f8fc;border-color:#cbdceb}.ficha-field{border-radius:4px;background:#f8fafc}.ficha-badge{border-radius:4px;background:#eaf2f9;border-color:#c8dbea;color:#204f76}.ficha-section--health .ficha-badge{background:#eaf2f9;border-color:#c8dbea;color:#204f76}.ficha-actions{justify-content:flex-start}.ficha-actions .btn{background:var(--tecm-blue);border-color:var(--tecm-blue)}@media(max-width:700px){.ficha-header{padding:24px}}
</style>
<style>
.ficha-body{display:grid;grid-template-columns:240px minmax(0,1fr);gap:26px;align-items:start;margin-top:16px}
.ficha-nav{position:sticky;top:92px;background:#fff;border:1px solid #dfe6ed;border-radius:6px;padding:16px 12px;box-shadow:0 2px 8px rgba(28,58,94,.05)}
.ficha-nav__title{margin:0 0 10px;padding:0 6px;font-size:.68rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#61758a}
.ficha-nav ol{list-style:none;margin:0;padding:0;counter-reset:fichanav}
.ficha-nav li{counter-increment:fichanav}
.ficha-nav a{display:flex;align-items:center;gap:9px;padding:8px 10px;border:1px solid transparent;border-radius:6px;color:#40566e;font-size:.84rem;line-height:1.3;text-decoration:none;transition:background .15s ease,border-color .15s ease,color .15s ease}
.ficha-nav a::before{content:counter(fichanav,decimal-leading-zero);flex:0 0 1.7rem;font-size:.68rem;font-weight:800;color:#9fb0c3;font-variant-numeric:tabular-nums}
.ficha-nav a:hover{background:#f4f8fc;color:var(--tecm-blue)}
.ficha-nav li.is-active a{background:#eef5fb;border-color:#c9dfef;color:var(--tecm-blue);font-weight:700}
.ficha-nav li.is-active a::before{color:var(--tecm-blue)}
.ficha-section{scroll-margin-top:96px}
html{scroll-behavior:smooth}
@media(max-width:900px){
    .ficha-body{grid-template-columns:1fr;gap:14px;margin-top:12px}
    .ficha-nav{position:sticky;top:58px;z-index:30;padding:10px;box-shadow:0 4px 14px rgba(28,58,94,.12)}
    .ficha-nav__title{display:none}
    .ficha-nav ol{display:flex;gap:6px;overflow-x:auto;scrollbar-width:thin;-webkit-overflow-scrolling:touch}
    .ficha-nav li{flex:0 0 auto}
    .ficha-nav a{white-space:nowrap;background:#f4f8fc;border-color:#e2eaf2}
    .ficha-section{scroll-margin-top:130px}
}
@media print{.ficha-nav{display:none!important}.ficha-body{display:block}}
</style>
@endpush

@push('scripts')
    <script>
        (function () {
            var nav = document.querySelector('.ficha-nav');
            if (!nav) return;
            var items = Array.prototype.slice.call(nav.querySelectorAll('li'));
            var targets = items.map(function (li) {
                var link = li.querySelector('a[href^="#ficha-"]');
                return link ? document.getElementById(decodeURIComponent(link.hash.slice(1))) : null;
            }).filter(Boolean);
            if (!targets.length) return;
            var setActive = function (id) {
                items.forEach(function (li) {
                    var link = li.querySelector('a');
                    li.classList.toggle('is-active', !!link && link.hash === '#' + id);
                });
            };
            var spy = function () {
                var current = targets[0].id;
                targets.forEach(function (target) {
                    if (target.getBoundingClientRect().top <= 140) current = target.id;
                });
                if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 60) {
                    current = targets[targets.length - 1].id;
                }
                setActive(current);
            };
            window.addEventListener('scroll', spy, { passive: true });
            window.addEventListener('resize', spy);
            spy();
        })();
    </script>
@endpush
