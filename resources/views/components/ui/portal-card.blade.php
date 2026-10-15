@props(['title', 'description', 'href', 'icon'])
<a class="portal-card" href="{{ url($href) }}"><span class="portal-card-icon"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i></span><span><strong>{{ $title }}</strong><small>{{ $description }}</small></span><i class="bi bi-arrow-up-right portal-card-arrow" aria-hidden="true"></i></a>
