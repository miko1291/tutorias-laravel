@php
    $studentTabs = [
        'perfil' => ['Mi perfil', '/expediente/perfil', 'bi-person'],
        'ficha' => ['Mi expediente', '/expediente/ficha', 'bi-folder2-open'],
        'formulario' => ['Actualizar expediente', '/expediente/index', 'bi-pencil-square'],
    ];
@endphp
<header class="student-page-heading no-print">
    <div>
        <span class="student-eyebrow">Programa institucional de tutorías</span>
        <h1>{{ $pageTitle }}</h1>
        <p>{{ $pageDescription }}</p>
    </div>
    <div class="student-heading-identity">
        <span class="student-mini-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($alumno->nombres, 0, 1)) }}</span>
        <div><strong>{{ $alumno->nombres }}</strong><span>Matrícula {{ $alumno->matricula }}</span></div>
    </div>
</header>
<nav class="student-page-tabs no-print" aria-label="Mi espacio de estudiante">
    @foreach ($studentTabs as $key => [$label, $path, $icon])
        <a href="{{ url($path) }}" class="student-page-tab {{ $activePage === $key ? 'is-current' : '' }}" @if ($activePage === $key) aria-current="page" @endif>
            <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
        </a>
    @endforeach
</nav>
