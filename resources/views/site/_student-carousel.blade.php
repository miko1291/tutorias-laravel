@php
    $slides = [
        ['image' => 'tuto1.png', 'eyebrow' => 'Comunidad ITSVA', 'title' => 'Tu trayectoria nos une', 'description' => 'Cada experiencia es una oportunidad para aprender, compartir y crecer.', 'alt' => 'Estudiantes del ITSVA en una actividad de la comunidad'],
        ['image' => 'tuto2.png', 'eyebrow' => 'Acompañamiento tutorial', 'title' => 'Avancemos juntos', 'description' => 'Acércate a tu tutora o tutor: encontrarás orientación para cada etapa.', 'alt' => 'Comunidad estudiantil del Instituto Tecnológico Superior de Valladolid'],
        ['image' => 'tuto3.png', 'eyebrow' => 'Vida estudiantil', 'title' => 'Forma parte de tu comunidad', 'description' => 'Participa, conecta con tus compañeros y construye nuevas experiencias.', 'alt' => 'Participación de estudiantes en actividades institucionales del ITSVA'],
    ];
@endphp
<section class="student-carousel" data-student-carousel role="region" aria-roledescription="carrusel" aria-label="Nuestra comunidad ITSVA">
    <div class="student-carousel-slides" id="studentCarouselSlides" aria-live="off">
        @foreach($slides as $slide)
            <article class="student-carousel-slide" data-carousel-slide role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ count($slides) }}" @if(!$loop->first) hidden @endif>
                <div class="student-carousel-copy"><span>{{ $slide['eyebrow'] }}</span><h2>{{ $slide['title'] }}</h2><p>{{ $slide['description'] }}</p></div>
                <div class="student-carousel-photo"><img src="{{ asset('images/'.$slide['image']) }}" alt="{{ $slide['alt'] }}" @if(!$loop->first) loading="lazy" @endif decoding="async"></div>
            </article>
        @endforeach
    </div>
    <div class="student-carousel-controls" hidden data-carousel-controls>
        <div class="student-carousel-dots" role="group" aria-label="Elegir fotografía">@foreach($slides as $slide)<button type="button" data-carousel-dot="{{ $loop->index }}" aria-label="Ver fotografía {{ $loop->iteration }}: {{ $slide['title'] }}" @if($loop->first) aria-current="true" @endif aria-controls="studentCarouselSlides"></button>@endforeach</div>
        <button class="student-carousel-pause" type="button" data-carousel-play aria-label="Reanudar presentación" aria-pressed="false" title="Reanudar presentación"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>
    </div>
</section>
