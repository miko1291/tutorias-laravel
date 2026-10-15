@auth
    <form id="logout-form" action="{{ route('logout') }}" method="post" hidden>
        @csrf
    </form>
@endauth
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app-shell.js') }}?v={{ filemtime(public_path('js/app-shell.js')) }}"></script>
